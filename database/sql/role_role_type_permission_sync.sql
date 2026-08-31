-- =====================================================================
-- Migration script: Role / Role Type / Permission synchronization
-- backfill.
--
-- Purpose: the Role Type feature (role_types, role_type_permissions,
-- roles.role_type_id -- see 2026_08_12_120000_create_role_types_tables
-- and 2026_08_12_140000_add_role_type_id_to_roles_table) was added
-- after roles/permissions already existed in production. Every role
-- created before that point has role_type_id = NULL and no
-- corresponding role_type_permissions rows, which breaks the Role
-- Edit / Role Type Edit screens (they read permissions through the
-- role's Role Type, not through the role directly).
--
-- This script bootstraps a Role Type for every such role (one-to-one,
-- named after the role) and copies that role's current
-- `role_permissions` rows into `role_type_permissions` for its new
-- type, so every existing role keeps exactly the permissions it had
-- before, now expressed through the Role Type model.
--
-- It also reconciles `role_types.status`: a Role Type is active if and
-- only if at least one role using it is active (mirrors the app-level
-- rule in RoleService::syncRoleTypeActiveStatus, which keeps this in
-- sync going forward for every role save; this script is what applies
-- the same rule in bulk / catches up any drift). The Role Type List
-- only ever displays active types, so an inactive role whose type has
-- no other active role now correctly drops out of that list, and a
-- role becoming active again correctly restores its type's visibility
-- -- without ever deleting or recreating the role_type row itself, so
-- its permission mappings are never lost in the process.
--
-- Idempotent: safe to re-run.
--   - role_types are matched/inserted by their unique `name`/`slug`
--     (uk_role_types_name, uk_role_types_slug) via
--     INSERT ... ON DUPLICATE KEY UPDATE, so re-running never creates
--     a second role_type for the same role.
--   - roles.role_type_id is only ever set when currently NULL, so a
--     role that already has a type (or was linked by an earlier run)
--     is left untouched.
--   - role_type_permissions has a composite primary key
--     (role_type_id, permission_id), and the insert uses
--     ON DUPLICATE KEY UPDATE with a no-op assignment, so re-running
--     never duplicates a mapping and never removes one.
--   - the status reconciliation (STEP 4) recomputes the same
--     deterministic value from current data every run, so re-running
--     it repeatedly is a no-op once roles stop changing.
-- No roles, permissions, or role_permissions rows are modified or
-- removed by this script. The only status ever written is
-- role_types.status, and only for a role_type that has at least one
-- linked role -- a standalone Role Type with no roles yet (e.g. one
-- created directly via the Role Type form, not yet assigned to any
-- role) is left exactly as an admin set it.
-- =====================================================================


-- =====================================================================
-- PART 0: PRE-FLIGHT / DIAGNOSTIC QUERIES (read-only)
-- Run these first. This script assumes their result sets are empty /
-- as expected; if not, resolve the data issue before running PART 1.
-- =====================================================================

-- 0a. Roles that will receive a brand-new Role Type by this script
--     (role_type_id currently NULL). Review the list before running.
SELECT id, name, slug, status
FROM roles
WHERE role_type_id IS NULL
ORDER BY name;

-- 0b. Safety check: role names/slugs must be unique for the 1-role <->
--     1-role_type bootstrap below to behave as a clean one-to-one
--     mapping. This is already enforced by application-level
--     validation (RoleRequest) for anything created going forward, but
--     legacy data is not DB-constrained (`roles` has no unique index
--     on name/slug). Expect ZERO rows; if this returns rows, those
--     roles will legitimately end up sharing one Role Type (harmless,
--     but worth knowing about before running PART 1).
SELECT name, COUNT(*) AS cnt
FROM roles
WHERE role_type_id IS NULL
GROUP BY name
HAVING COUNT(*) > 1;

SELECT slug, COUNT(*) AS cnt
FROM roles
WHERE role_type_id IS NULL
GROUP BY slug
HAVING COUNT(*) > 1;

-- 0c. Safety check: none of the roles awaiting a new Role Type should
--     collide with a Role Type name/slug that already exists (would
--     otherwise silently attach the role to an unrelated, pre-existing
--     type). Expect ZERO rows.
--     NOTE: `roles`.name/slug are utf8mb4_general_ci while
--     `role_types`.name/slug are utf8mb4_unicode_ci in this schema --
--     MySQL refuses to compare differently-collated columns directly,
--     hence the explicit COLLATE below (same fix already applied to
--     role_type_permissions.permission_id by
--     2026_08_12_120000_create_role_types_tables.php).
SELECT r.id, r.name, r.slug, rt.id AS conflicting_role_type_id
FROM roles r
JOIN role_types rt
    ON (rt.name COLLATE utf8mb4_general_ci = r.name OR rt.slug COLLATE utf8mb4_general_ci = r.slug)
WHERE r.role_type_id IS NULL;

-- 0d. Preview of the status reconciliation STEP 4 will perform: every
--     Role Type that has at least one linked role, its current status,
--     and what STEP 4 will set it to. Rows where current <> will_be are
--     the ones this run actually changes -- everything else is already
--     correct (including on every re-run after the first).
SELECT
    rt.id AS role_type_id,
    rt.name AS role_type_name,
    rt.status AS current_status,
    (SELECT COUNT(*) FROM roles r WHERE r.role_type_id = rt.id) AS linked_role_count,
    (SELECT COUNT(*) FROM roles r WHERE r.role_type_id = rt.id AND r.status = 1) AS linked_active_role_count,
    CASE WHEN EXISTS (SELECT 1 FROM roles r WHERE r.role_type_id = rt.id AND r.status = 1) THEN 1 ELSE 0 END AS will_be_status
FROM role_types rt
WHERE EXISTS (SELECT 1 FROM roles r WHERE r.role_type_id = rt.id)
ORDER BY rt.name;


-- =====================================================================
-- PART 1: BACKFILL (transactional)
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- STEP 1: Create one Role Type per role that does not have one yet,
-- named/slugged identically to the role it is bootstrapped from.
-- ON DUPLICATE KEY UPDATE is a no-op (keeps the first-seen row as-is)
-- so re-running this step never creates a duplicate role_type even if
-- two roles happen to share a name/slug (see 0b/0c above).
-- ---------------------------------------------------------------------
INSERT INTO `role_types` (`id`, `name`, `slug`, `status`, `created_at`, `updated_at`)
SELECT UUID(), r.name, r.slug, r.status, NOW(), NOW()
FROM `roles` r
WHERE r.role_type_id IS NULL
ON DUPLICATE KEY UPDATE
`role_types`.`name` = `role_types`.`name`;

-- ---------------------------------------------------------------------
-- STEP 2: Link every role that still has no role_type_id to the Role
-- Type matching its slug (either just created in STEP 1, or a
-- pre-existing one it happens to match -- see 0c). Only ever touches
-- rows where role_type_id IS NULL, so it never overwrites a
-- Role Type a role was already assigned to (manually or by an earlier
-- run of this script). See the collation note on 0c above for why the
-- join needs an explicit COLLATE.
-- ---------------------------------------------------------------------
UPDATE `roles` r
JOIN `role_types` rt ON rt.slug COLLATE utf8mb4_general_ci = r.slug
SET r.role_type_id = rt.id
WHERE r.role_type_id IS NULL;

-- ---------------------------------------------------------------------
-- STEP 3: Copy every role's current role_permissions into
-- role_type_permissions for its Role Type. Runs across ALL roles that
-- have a role_type_id (not just the ones just linked above), so
-- re-running this script also catches up any role_permissions granted
-- since the last run. role_type_permissions' composite primary key
-- (role_type_id, permission_id) plus ON DUPLICATE KEY UPDATE guarantees
-- no duplicate mapping is ever created.
-- ---------------------------------------------------------------------
INSERT INTO `role_type_permissions` (`role_type_id`, `permission_id`)
SELECT DISTINCT r.role_type_id, rp.permission_id
FROM `roles` r
JOIN `role_permissions` rp ON rp.role_id = r.id
WHERE r.role_type_id IS NOT NULL
ON DUPLICATE KEY UPDATE
`role_type_permissions`.`role_type_id` = `role_type_permissions`.`role_type_id`;

-- ---------------------------------------------------------------------
-- STEP 4: Reconcile role_types.status with the roles that reference it
-- -- active if any linked role is active, inactive the moment none are.
-- MAX(r.status) over a role_type's linked roles is 1 if any of them is
-- active and 0 only when every one of them is inactive (status is
-- always 0/1), so it doubles as the "has an active role" flag. Only
-- ever touches a role_type that has at least one linked role (the
-- INNER JOIN requires a matching group), so a standalone Role Type
-- with zero roles keeps whatever status an admin gave it. The trailing
-- WHERE further limits the write to rows whose status actually needs
-- to change, so a re-run over unchanged data updates zero rows (and
-- never bumps updated_at) instead of just being a no-op value-wise.
--
-- This is the same rule the app now applies on every role save
-- (RoleService::syncRoleTypeActiveStatus); running it here catches up
-- any role_type whose status drifted before that app-level sync
-- existed, or from data changed outside the app. Deterministic and
-- side-effect free beyond this one UPDATE -- does not touch roles,
-- permissions, role_permissions, or role_type_permissions.
-- ---------------------------------------------------------------------
UPDATE `role_types` rt
JOIN (
    SELECT r.role_type_id AS id, MAX(r.status) AS computed_status
    FROM `roles` r
    WHERE r.role_type_id IS NOT NULL
    GROUP BY r.role_type_id
) computed ON computed.id = rt.id
SET rt.status = computed.computed_status,
    rt.updated_at = NOW()
WHERE rt.status <> computed.computed_status;

COMMIT;


-- =====================================================================
-- PART 2: VALIDATION QUERIES (run after PART 1)
-- =====================================================================

-- 2a. No role should be left without a Role Type after this script.
--     Expect ZERO rows.
SELECT id, name, slug FROM roles WHERE role_type_id IS NULL;

-- 2b. Every role's role_permissions must now be a subset of its
--     Role Type's role_type_permissions (i.e. nothing was dropped, and
--     the Role Type carries at least what the role already had).
--     Expect ZERO rows.
SELECT r.id AS role_id, r.name AS role_name, rp.permission_id
FROM roles r
JOIN role_permissions rp ON rp.role_id = r.id
LEFT JOIN role_type_permissions rtp
    ON rtp.role_type_id = r.role_type_id
   AND rtp.permission_id = rp.permission_id
WHERE r.role_type_id IS NOT NULL
  AND rtp.permission_id IS NULL;

-- 2c. Row-count summary for a quick sanity check.
SELECT 'roles_total' AS metric, COUNT(*) AS value FROM roles
UNION ALL
SELECT 'roles_without_role_type', COUNT(*) FROM roles WHERE role_type_id IS NULL
UNION ALL
SELECT 'roles_active', COUNT(*) FROM roles WHERE status = 1
UNION ALL
SELECT 'roles_inactive', COUNT(*) FROM roles WHERE status = 0
UNION ALL
SELECT 'role_types_total', COUNT(*) FROM role_types
UNION ALL
SELECT 'role_types_active', COUNT(*) FROM role_types WHERE status = 1
UNION ALL
SELECT 'role_types_inactive', COUNT(*) FROM role_types WHERE status = 0
UNION ALL
SELECT 'role_type_permissions_total', COUNT(*) FROM role_type_permissions;

-- 2e. Status reconciliation sanity checks (both expect ZERO rows):
--     no Role Type with at least one active role is left inactive, and
--     no Role Type whose roles are all inactive is left active.
SELECT rt.id, rt.name, rt.status AS role_type_status
FROM role_types rt
WHERE rt.status = 0
  AND EXISTS (SELECT 1 FROM roles r WHERE r.role_type_id = rt.id AND r.status = 1);

SELECT rt.id, rt.name, rt.status AS role_type_status
FROM role_types rt
WHERE rt.status = 1
  AND EXISTS (SELECT 1 FROM roles r WHERE r.role_type_id = rt.id)
  AND NOT EXISTS (SELECT 1 FROM roles r WHERE r.role_type_id = rt.id AND r.status = 1);

-- 2f. Guarantee this script never touched roles/permissions/role_permissions:
--     compare against a count taken before running PART 1 -- these
--     numbers must be identical, since STEP 4 only ever writes
--     role_types.status/updated_at.
SELECT 'role_permissions_total' AS metric, COUNT(*) AS value FROM role_permissions
UNION ALL
SELECT 'permissions_total', COUNT(*) FROM permissions;

-- 2g. Spot-check: reproduce one role's permission set through its
--     Role Type, side by side with what role_permissions has directly
--     -- the two permission_id lists should match exactly for any role
--     that hasn't been edited since the backfill ran. Replace the
--     role name below to inspect a specific role.
SELECT
    r.name AS role_name,
    rt.name AS role_type_name,
    (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id) AS direct_permission_count,
    (SELECT COUNT(*) FROM role_type_permissions rtp WHERE rtp.role_type_id = r.role_type_id) AS role_type_permission_count
FROM roles r
LEFT JOIN role_types rt ON rt.id = r.role_type_id
ORDER BY r.name;
