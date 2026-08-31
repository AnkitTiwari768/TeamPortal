-- =====================================================================
-- select_last_1_month.sql
-- Reporting queries: module permissions and role mappings changed in the
-- last 1 month in database `team_portal47`.
--
-- Tables involved (see insert_last_1_month.sql header for full schema notes):
--   modules                  - id PK, created_at/updated_at present
--   permissions               - id PK, module_id -> modules.id, created_at/updated_at present
--   roles                     - id PK, role_type_id -> role_types.id (enforced FK),
--                               created_at/updated_at present
--   role_types                - id PK, created_at/updated_at present
--   role_permissions          - (role_id, permission_id) composite PK, junction
--                               table, NO timestamp columns
--   permission_role_mapping   - (permission_id, role_id) composite PK, junction
--                               table (reverse-direction mapping), NO timestamp columns
--
-- IMPORTANT: role_permissions and permission_role_mapping carry no created_at /
-- updated_at columns at all, so "changed in the last 1 month" for a mapping
-- row is approximated as: the mapping references a permission OR a role that
-- was itself created/updated in the last 1 month. This is the closest signal
-- available in the current schema (there is no dedicated audit/history table
-- for these two junction tables).
--
-- EXCLUDED: the test role 'ankit test' (id 'a27bea87-53dd-4808-9d1c-abba9f39d5bf')
-- is filtered out of every query below, along with any role_permissions /
-- permission_role_mapping rows tied to it, at request.
-- =====================================================================

SET @since = DATE_SUB(NOW(), INTERVAL 1 MONTH);
-- NOTE: the excluded role id below is inlined as a literal (rather than a
-- session variable) in each query, because this schema mixes collations
-- across tables (roles.id is utf8mb4_bin, permissions/role_permissions use
-- utf8mb4_general_ci) - a user variable picks up the connection's collation
-- and errors with "Illegal mix of collations" against some of these columns,
-- while a literal string constant coerces to whichever column it's compared
-- against.

-- ---------------------------------------------------------------------
-- 1) Modules created or updated in the last 1 month
-- ---------------------------------------------------------------------
SELECT
    m.id,
    m.parent_id,
    pm.name AS parent_module_name,
    m.name AS module_name,
    m.slug AS module_slug,
    m.url,
    m.module_type,
    m.type,
    m.status,
    m.created_at,
    m.updated_at,
    m.created_by,
    m.updated_by
FROM modules m
LEFT JOIN modules pm ON pm.id = m.parent_id
WHERE m.created_at >= @since
   OR m.updated_at >= @since
ORDER BY m.created_at;

-- ---------------------------------------------------------------------
-- 2) Permissions created or updated in the last 1 month (with owning module)
-- ---------------------------------------------------------------------
SELECT
    p.id,
    p.name AS permission_name,
    p.slug AS permission_slug,
    p.description,
    p.module_id,
    mo.name AS module_name,
    mo.slug AS module_slug,
    p.status,
    p.show_to_custom_user,
    p.created_at,
    p.updated_at
FROM permissions p
LEFT JOIN modules mo ON mo.id = p.module_id
WHERE p.created_at >= @since
   OR p.updated_at >= @since
ORDER BY p.created_at;

-- ---------------------------------------------------------------------
-- 3) Roles created or updated in the last 1 month
-- ---------------------------------------------------------------------
SELECT
    r.id,
    r.name AS role_name,
    r.slug AS role_slug,
    r.description,
    r.status,
    r.role_type_id,
    rt.name AS role_type_name,
    r.department_id,
    r.is_custom,
    r.is_organizer,
    r.can_view_workshop,
    r.created_by,
    r.updated_by,
    r.created_at,
    r.updated_at
FROM roles r
LEFT JOIN role_types rt ON rt.id = r.role_type_id
WHERE (r.created_at >= @since OR r.updated_at >= @since)
  AND r.id <> 'a27bea87-53dd-4808-9d1c-abba9f39d5bf'
ORDER BY r.created_at;

-- ---------------------------------------------------------------------
-- 4) role_permissions mappings touching a recently changed role or permission
--    (role -> permission direction; drives ACL/session permission checks)
-- ---------------------------------------------------------------------
SELECT
    rp.role_id,
    r.name AS role_name,
    rp.permission_id,
    p.name AS permission_name,
    p.slug AS permission_slug,
    p.module_id,
    mo.name AS module_name,
    (p.created_at >= @since OR p.updated_at >= @since) AS permission_recently_changed,
    (r.created_at >= @since OR r.updated_at >= @since) AS role_recently_changed
FROM role_permissions rp
JOIN roles r ON r.id = rp.role_id
JOIN permissions p ON p.id = rp.permission_id
LEFT JOIN modules mo ON mo.id = p.module_id
WHERE ((p.created_at >= @since OR p.updated_at >= @since)
   OR (r.created_at >= @since OR r.updated_at >= @since))
  AND rp.role_id <> 'a27bea87-53dd-4808-9d1c-abba9f39d5bf'
ORDER BY role_name, permission_name;

-- ---------------------------------------------------------------------
-- 5) permission_role_mapping mappings touching a recently changed role or
--    permission (permission -> role direction; drives the admin "roles for
--    this permission" screen)
-- ---------------------------------------------------------------------
SELECT
    prm.permission_id,
    p.name AS permission_name,
    p.slug AS permission_slug,
    p.module_id,
    mo.name AS module_name,
    prm.role_id,
    r.name AS role_name,
    (p.created_at >= @since OR p.updated_at >= @since) AS permission_recently_changed,
    (r.created_at >= @since OR r.updated_at >= @since) AS role_recently_changed
FROM permission_role_mapping prm
JOIN permissions p ON p.id = prm.permission_id
LEFT JOIN modules mo ON mo.id = p.module_id
LEFT JOIN roles r ON r.id = prm.role_id
WHERE ((p.created_at >= @since OR p.updated_at >= @since)
   OR (r.created_at >= @since OR r.updated_at >= @since))
  AND prm.role_id <> 'a27bea87-53dd-4808-9d1c-abba9f39d5bf'
ORDER BY permission_name, role_name;
