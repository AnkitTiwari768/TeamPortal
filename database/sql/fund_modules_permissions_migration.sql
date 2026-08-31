-- =====================================================================
-- Migration script: Workshop / Fund Flow / Fund Distribution /
-- Fund Flow Configuration / AI Cataloguing Claim
-- modules, permissions, roles, and role-permission mappings.
--
-- Source: team_portal47 (production)
-- Target: any fresh/another database with the same schema
--
-- Idempotent: safe to re-run. Uses INSERT ... ON DUPLICATE KEY UPDATE
-- throughout, so existing rows are updated in place and missing rows
-- are inserted -- no duplicates are ever created.
--
-- No column names, permission slugs, module names, or IDs were
-- changed or invented -- every row below is copied verbatim from the
-- source database. No new roles or permissions were created.
--
-- Verified: this exact script was executed twice in a row against a
-- clean throwaway schema (same table structures, no data) -- the
-- second run produced identical row counts, confirming idempotency.
-- =====================================================================


-- =====================================================================
-- PART 0: SOURCE RECORD IDENTIFICATION
-- (read-only queries used against the SOURCE database to identify and
-- extract every record migrated below -- run these first if you need
-- to re-derive or audit the row set)
-- =====================================================================

-- 0a. Resolve the module ids for the 5 requested feature areas.
--     NOTE: the modules table has no "feature group" column, so module
--     trees are identified by name/slug and then walked via parent_id.
--     Two ambiguities were found in the source data and are called out
--     explicitly rather than silently picked for you:
--       * "Fund Flow" itself (slug fund-flow) is an empty top-level nav
--         placeholder with ZERO permissions attached. The permissions
--         actually used by the live Fund Allocation feature live under
--         a separate top-level module literally named "Fund Allocation"
--         (slug fund-allocation, url fund-allocationss-old) and its two
--         children ("New Allocation" -> fund-allocation/create,
--         "All Allocations" -> fund-allocations), which match the live
--         application routes exactly. Both trees are included below.
--       * "Workshop" matches TWO distinct top-level modules that both
--         happen to share the slug `workshop-management`:
--           - "Explore Workshops" (1 permission: explore-workshop-view)
--           - "Workshop Management" (9 permissions, children "Proposed
--             Workshop" and "Executed Workshop")
--         Both are included below since both exist in the source data.
SELECT id, parent_id, name, slug, url, sort_order, status
FROM modules
WHERE name IN (
    'Fund Flow', 'Fund Allocation', 'New Allocation', 'All Allocations',
    'Fund Distribution', 'New Distribution', 'All Distributions',
    'Fund Flow Configuration', 'Configuration Home',
    'Component Utilization Mapping', 'Carry Forward Mapping',
    'AI Cataloguing Claim', 'File a Claim', 'View Your Claim',
    'Workshop Management', 'Proposed Workshop', 'Executed Workshop',
    'Explore Workshops'
)
ORDER BY sort_order;

-- 0b. Given the module id set above (@module_ids), pull every permission
--     attached to any of those modules.
SELECT p.id, p.name, p.slug, p.description, p.module_id, p.status
FROM permissions p
WHERE p.module_id IN (/* @module_ids from 0a */)
ORDER BY p.module_id;

-- 0c. Given the permission id set above (@permission_ids), pull every
--     role that is mapped to any of those permissions, from BOTH
--     mapping tables that exist in this schema:
--       - role_permissions           (drives the live login/ACL session
--                                      -- see App\Http\Api\V1\Auth\AuthRepository)
--       - permission_role_mapping    (drives the "view roles for this
--                                      permission" admin screen -- see
--                                      App\Http\Api\V1\Permission\PermissionRole)
--     Both are genuinely used by the application, so both are migrated.
SELECT DISTINCT r.id, r.name, r.slug, r.status
FROM roles r
WHERE r.id IN (
    SELECT role_id FROM role_permissions WHERE permission_id IN (/* @permission_ids from 0b */)
    UNION
    SELECT role_id FROM permission_role_mapping WHERE permission_id IN (/* @permission_ids from 0b */)
);

-- 0d. The actual mapping rows to migrate.
SELECT * FROM role_permissions WHERE permission_id IN (/* @permission_ids from 0b */);
SELECT * FROM permission_role_mapping WHERE permission_id IN (/* @permission_ids from 0b */);


-- =====================================================================
-- PART 1: DEPENDENCY / FOREIGN KEY NOTE
-- =====================================================================
-- None of modules / permissions / roles / role_permissions /
-- permission_role_mapping have DB-enforced FOREIGN KEY constraints in
-- this schema (verified via SHOW CREATE TABLE) -- the relationships
-- (permissions.module_id -> modules.id, role_permissions.role_id ->
-- roles.id, role_permissions.permission_id -> permissions.id, and the
-- mirrored columns in permission_role_mapping) are enforced only at
-- the application layer. There is therefore no DB-level ordering
-- requirement, but the statements below still follow the required
-- parent-child order for referential sanity and to make the script
-- valid on any target that DOES have FKs defined:
--   1) modules  2) permissions  3) roles  4) role/permission mappings
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- STEP 1: MODULES (18 rows)
-- ---------------------------------------------------------------------
INSERT INTO `modules` (`id`, `parent_id`, `module_type`, `name`, `sort_order`, `slug`, `url`, `second_url`, `icon`, `color`, `type`, `description`, `status`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
('a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8', NULL, NULL, 'Explore Workshops', '0', 'workshop-management', 'explore-workshops', NULL, 'doc.svg', NULL, '1', NULL, '1', '2026-01-20 12:05:45', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1accda0-af9e-4110-a0c2-e645c0715aa0', 'a0606186-f978-46e5-b1fb-c5359e156424', NULL, 'Proposed Workshop', '0', 'proposed-workshop', 'proposed-workshop', NULL, 'doc.svg', NULL, '1', NULL, '1', '2026-05-01 17:09:23', '2026-07-16 12:58:18', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1b25554-79d9-445a-9fca-80d6ddb897ea', 'a0606186-f978-46e5-b1fb-c5359e156424', NULL, 'Executed Workshop', '1', 'executed-workshop', 'executed-workshop', NULL, 'doc.svg', NULL, '1', NULL, '1', '2026-05-04 11:07:58', '2026-07-16 15:08:26', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, NULL, 'AI Cataloguing Claim', '20', 'ai-cataloguing-claim', 'ai-cat', NULL, 'fa fa-cog', NULL, '1', NULL, '1', '2026-07-28 15:30:31', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('a25db194-86fe-4b81-b4e2-cd7e4dbd8001', 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, 'File a Claim', '22', 'file-a-claim', 'file-ai-cataloguing-claim', NULL, 'fa fa-cog', NULL, '1', NULL, '1', '2026-07-28 15:32:29', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('a25db211-d409-4cde-bc6e-ee3ad3dee86b', 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, 'View Your Claim', '24', 'view-your-claim', 'ai-cataloguing-claim', NULL, 'fa fa-cog', NULL, '1', NULL, '1', '2026-07-28 15:33:51', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, NULL, 'Fund Allocation', '30', 'fund-allocation', 'fund-allocationss-old', NULL, 'dashboard.svg', NULL, '1', NULL, '1', '2025-08-20 14:38:11', '2026-07-17 17:32:07', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, 'New Allocation', '30', 'new-allocation', 'fund-allocation/create', NULL, 'document', NULL, '1', NULL, '1', '2026-05-14 11:27:27', '2026-07-17 17:26:53', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, 'All Allocations', '30', 'all-allocations', 'fund-allocations', NULL, 'document', NULL, '1', NULL, '1', '2026-05-14 11:28:41', '2026-07-17 17:32:14', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('9fb76cd8-7f35-4ce6-8a4e-bced614ed6db', NULL, NULL, 'Fund Distribution', '40', 'fund-distribution', 'fund-distributions-old', NULL, 'document.svg', NULL, '1', NULL, '1', '2025-08-25 11:28:59', '2026-07-18 14:30:44', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1c67b7a-f62e-4288-b041-28680641ba7f', '9fb76cd8-7f35-4ce6-8a4e-bced614ed6db', NULL, 'New Distribution', '40', 'new-distribution', 'fund-distributions/create', NULL, 'document.svg', NULL, '1', NULL, '1', '2026-05-14 11:31:12', '2026-07-18 14:29:37', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a1c67c35-d019-4f8f-8e5d-d9163567a254', '9fb76cd8-7f35-4ce6-8a4e-bced614ed6db', NULL, 'All Distributions', '40', 'all-distributions', 'fund-distributions', NULL, 'document.svg', NULL, '1', NULL, '1', '2026-05-14 11:33:14', '2026-07-18 14:31:12', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a2472cdf-1568-477a-80ee-78066ab146ad', NULL, NULL, 'Fund Flow Configuration', '42', 'fund-flow-configuration', 'fund-flow-configuration', NULL, 'fa fa-cog', NULL, '1', NULL, '1', '2026-07-17 10:53:11', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('a2472d62-4663-4673-897f-8ab7f26d7f0d', 'a2472cdf-1568-477a-80ee-78066ab146ad', NULL, 'Configuration Home', '42', 'configuration-home', 'configuration-home', NULL, 'fa fa-home', NULL, '1', NULL, '1', '2026-07-17 10:54:37', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('a2472db8-bf19-4bdb-820e-4fe02686e7ef', 'a2472cdf-1568-477a-80ee-78066ab146ad', NULL, 'Component Utilization Mapping', '43', 'component-utilization-mapping', 'component-utilization-mapping', NULL, 'fa fa-home', NULL, '1', NULL, '1', '2026-07-17 10:55:34', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('a2472e03-7884-4221-b125-fa6fda7ab9b6', 'a2472cdf-1568-477a-80ee-78066ab146ad', NULL, 'Carry Forward Mapping', '44', 'carry-forward-mapping', 'carry-forward-mapping', NULL, 'fa fa-home', NULL, '1', NULL, '1', '2026-07-17 10:56:23', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL),
('9fada167-b216-43ff-909e-abe2c41a574e', NULL, NULL, 'Fund Flow', '65', 'fund-flow', 'fund-flow', NULL, 'document.svg', NULL, '1', NULL, '1', '2025-08-20 14:37:40', '2026-01-27 21:36:24', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'),
('a0606186-f978-46e5-b1fb-c5359e156424', NULL, NULL, 'Workshop Management', '70', 'workshop-management', '##########', NULL, 'document.svg', NULL, '1', NULL, '1', '2025-11-17 11:12:15', '2026-05-01 17:08:16', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945')
ON DUPLICATE KEY UPDATE
`parent_id` = VALUES(`parent_id`),
`module_type` = VALUES(`module_type`),
`name` = VALUES(`name`),
`sort_order` = VALUES(`sort_order`),
`slug` = VALUES(`slug`),
`url` = VALUES(`url`),
`second_url` = VALUES(`second_url`),
`icon` = VALUES(`icon`),
`color` = VALUES(`color`),
`type` = VALUES(`type`),
`description` = VALUES(`description`),
`status` = VALUES(`status`),
`created_at` = VALUES(`created_at`),
`updated_at` = VALUES(`updated_at`),
`created_by` = VALUES(`created_by`),
`updated_by` = VALUES(`updated_by`);

-- ---------------------------------------------------------------------
-- STEP 2: PERMISSIONS (28 rows)
-- ---------------------------------------------------------------------
INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `module_id`, `show_to_custom_user`, `status`, `created_at`, `updated_at`) VALUES
('a1c67da1-fce0-46d4-bc5e-81fdddb17e2c', 'Fund Allocation Create', 'fund-allocation-create', 'allocation-create', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, '1', '2026-05-14 11:37:13', '2026-07-17 18:20:06'),
('a1c67e17-a12e-4ebc-a488-8276e351d81b', 'Allocation-view', 'allocation-view', 'allocation-view', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, '1', '2026-05-14 11:38:30', '2026-07-17 18:21:52'),
('a1c67e5b-ce7c-487a-91ff-f16cfce2e264', 'Allocation-update', 'allocation-update', 'allocation-update', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, '1', '2026-05-14 11:39:15', '2026-07-17 18:22:17'),
('a0e13539-0836-416d-a360-17774c7b6083', 'Explore Workshop View', 'explore-workshop-view', 'Explore Workshop View', 'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8', NULL, '1', '2026-01-20 12:10:10', '2026-01-20 16:46:53'),
('a060621f-ef43-4ce8-be1f-41c7d36dcf9a', 'Workshop-management-create', 'workshop-management-create', 'create permission for workshop-management-create', 'a1accda0-af9e-4110-a0c2-e645c0715aa0', NULL, '1', '2025-11-17 11:13:55', '2026-07-19 15:48:42'),
('a0606255-fd84-4257-bef2-e382f14986d8', 'Workshop-management-view', 'workshop-management-view', 'create permission for workshop-management-view', 'a1accda0-af9e-4110-a0c2-e645c0715aa0', NULL, '1', '2025-11-17 11:14:31', '2026-07-19 15:47:17'),
('a0ab3a65-3634-4680-980e-95d5dbbcf1d1', 'Workshop-management-edit', 'workshop-management-edit', '', 'a1accda0-af9e-4110-a0c2-e645c0715aa0', NULL, '1', '2025-12-24 16:09:55', '2026-07-19 15:45:30'),
('a0ab3a96-5707-40dd-9a14-712738b6dbd7', 'Workshop-management-delete', 'workshop-management-delete', '', 'a1accda0-af9e-4110-a0c2-e645c0715aa0', NULL, '1', '2025-12-24 16:10:27', '2026-07-19 15:46:39'),
('a1b2559e-1436-4231-9934-4f13446410bf', 'Executed-workshop-view', 'executed-workshop-view', 'Executed Workshop View', 'a1b25554-79d9-445a-9fca-80d6ddb897ea', NULL, '1', '2026-05-04 11:08:46', '2026-07-17 10:55:46'),
('a1b255c3-915e-4687-93f0-9ee408137c4e', 'Executed-workshop-create', 'executed-workshop-create', 'Executed Workshop Create', 'a1b25554-79d9-445a-9fca-80d6ddb897ea', NULL, '1', '2026-05-04 11:09:11', '2026-07-17 10:52:06'),
('a1b255e7-d12d-41a0-aeb3-3aca64292fc4', 'Executed-workshop-edit', 'executed-workshop-edit', 'Executed Workshop Edit', 'a1b25554-79d9-445a-9fca-80d6ddb897ea', NULL, '1', '2026-05-04 11:09:35', '2026-07-17 10:52:20'),
('a1b25606-49f0-4ff8-8bc3-6c085c00eb16', 'Executed-workshop-delete', 'executed-workshop-delete', 'Executed Workshop Delete', 'a1b25554-79d9-445a-9fca-80d6ddb897ea', NULL, '1', '2026-05-04 11:09:55', '2026-07-17 10:52:33'),
('a2474e30-7849-4ee0-a4a1-42f3ec8d28c5', 'Executed-workshop-status', 'executed-workshop-status', 'Executed-workshop-status', 'a1b25554-79d9-445a-9fca-80d6ddb897ea', NULL, '1', '2026-07-17 12:26:21', '2026-07-17 12:28:22'),
('9fada2a6-bb06-4600-8d85-6c117d7ee62c', 'Fund-allocation-create', 'fund-allocation-create', 'fund-allocation-create', 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', NULL, '1', '2025-08-20 14:41:09', '2026-07-17 18:32:44'),
('a1c67d69-dd31-4aad-b751-3aa550e03972', 'Fund Allocation View', 'fund-allocation-view', 'New-Allocation-view', 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', NULL, '1', '2026-05-14 11:36:36', '2026-07-17 18:36:46'),
('a247beec-4875-464f-9da9-fc3be93150a0', 'Fund-allocation-edit', 'fund-allocation-edit', 'Fund-allocation-list', 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', NULL, '1', '2026-07-17 17:41:35', '2026-07-17 18:33:03'),
('a247bf23-1feb-46cd-8bf1-e671836a6fad', 'Fund-allocation-delete', 'fund-allocation-delete', 'Fund-allocation-delete', 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', NULL, '1', '2026-07-17 17:42:11', '2026-07-17 18:33:27'),
('9fada28a-467b-40d4-b775-31c12083e2dc', 'Fund-allocation-view', 'fund-allocation-view', 'fund-allocation-view', 'a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9', NULL, '1', '2025-08-20 14:40:51', '2026-07-17 18:34:15'),
('9fb76d88-24a4-430c-a8cb-be2f1e3f02b3', 'Fund-distribution-create', 'fund-distribution-create', 'Fund-distribution-create', 'a1c67b7a-f62e-4288-b041-28680641ba7f', NULL, '1', '2025-08-25 11:30:54', '2026-05-15 15:07:53'),
('a1c67eaa-e79b-4ed7-ac86-83770e388626', 'New-fund-distribution-view', 'new-fund-distribution-view', 'new-fund-distribution-view', 'a1c67b7a-f62e-4288-b041-28680641ba7f', NULL, '1', '2026-05-14 11:40:07', '2026-05-15 15:08:25'),
('9fb76d5e-f2ae-4d59-99e6-fd54d430ab6f', 'Fund-distribution-view', 'fund-distribution-view', 'fund-distribution-view', 'a1c67c35-d019-4f8f-8e5d-d9163567a254', NULL, '1', '2025-08-25 11:30:27', '2026-05-15 15:07:18'),
('a24d382c-d8b2-47d1-8f62-fcf2840b15da', 'Configuration Home View', 'configuration-home-view', 'Configuration Home View', 'a2472d62-4663-4673-897f-8ab7f26d7f0d', NULL, '1', '2026-07-20 10:59:46', '2026-07-20 10:59:46'),
('a24da36c-1067-42f2-b191-a77f524ebde5', 'Component Utilization Mapping View', 'component-utilization-mapping-view', '', 'a2472db8-bf19-4bdb-820e-4fe02686e7ef', NULL, '1', '2026-07-20 15:59:39', '2026-07-20 15:59:39'),
('a24da39e-d289-4537-9c37-2150237155a4', 'Component Utilization Mapping Create', 'component-utilization-mapping-create', '', 'a2472db8-bf19-4bdb-820e-4fe02686e7ef', NULL, '1', '2026-07-20 16:00:12', '2026-07-20 16:00:12'),
('a24d3fc4-cbee-4896-81ff-fb18bbdc580d', 'Carry Forward View', 'carry-forward-view', 'Carry Forward View', 'a2472e03-7884-4221-b125-fa6fda7ab9b6', NULL, '1', '2026-07-20 11:21:00', '2026-07-20 11:21:00'),
('a24d6329-669e-472c-a920-122e436b7ae6', 'Carry Forward Create', 'carry-forward-create', '', 'a2472e03-7884-4221-b125-fa6fda7ab9b6', NULL, '1', '2026-07-20 12:59:58', '2026-07-20 12:59:58'),
('a25db4bf-8396-4209-ae2b-81e5ddd66029', 'File A Claim View', 'file-a-claim-view', '', 'a25db194-86fe-4b81-b4e2-cd7e4dbd8001', NULL, '1', '2026-07-28 15:41:21', '2026-07-28 15:57:18'),
('a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'AI Cataloguing Claim View', 'ai-cataloguing-claim-view', '', 'a25db211-d409-4cde-bc6e-ee3ad3dee86b', NULL, '1', '2026-07-28 15:43:32', '2026-07-28 15:45:35')
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`slug` = VALUES(`slug`),
`description` = VALUES(`description`),
`module_id` = VALUES(`module_id`),
`show_to_custom_user` = VALUES(`show_to_custom_user`),
`status` = VALUES(`status`),
`created_at` = VALUES(`created_at`),
`updated_at` = VALUES(`updated_at`);

-- ---------------------------------------------------------------------
-- STEP 3: ROLES (10 rows -- only roles already mapped to the
-- permissions above; no roles were created)
-- ---------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `status`, `department_id`, `is_custom`, `is_organizer`, `can_view_workshop`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
('547a585d-8e0d-11f0-81a4-00155d022d06', 'Buyer Network Participant (BNP)', 'bnp', 'aassaas', '1', '99f3541c-a284-427d-b77a-3ff2062e8a20', NULL, NULL, '1', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-07-01 17:56:43', '2025-09-26 12:12:50'),
('99bf20e7-3ad0-4423-836a-a1ea6929e704', 'Administrator', 'administrator', NULL, '1', NULL, NULL, NULL, NULL, '', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '0000-00-00 00:00:00', '2023-12-20 16:13:09'),
('9f495362-2497-452e-991c-2d2b83872242', 'Seller Network Participant (SNP)', 'snp', NULL, '1', '99f3541c-a284-427d-b77a-3ff2062e8a20', NULL, NULL, '1', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-07-01 17:56:43', '2025-07-01 18:32:09'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'ONDC Admin', 'ondc-admin', NULL, '1', '57b172e6-efef-11ee-8177-00155d022d06', NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-04 12:55:39', '2025-08-04 12:55:57'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'NSIC', 'nsic', NULL, '1', '9bf41ad0-acc9-4072-b50e-7fc66a5a0d5a', NULL, '1', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-04 12:56:36', '2025-08-04 12:56:36'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', 'MO MSE', 'mo-mse', NULL, '1', '9bf41ad0-acc9-4072-b50e-7fc66a5a0d5a', NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-09-22 11:55:37', '2025-09-26 12:15:20'),
('a08ea766-4ff4-4e4f-8115-74af7100b667', 'Logistics Service Provider (LSP)', 'lsp', NULL, '1', 'a08ea750-941b-4820-a97d-59ba683cda5c', NULL, NULL, '1', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-12-10 11:15:42', '2025-12-10 11:15:42'),
('a0a9a624-c536-4989-8db9-0bacec0364bb', 'MSME', 'msme', NULL, '1', 'a0a9a60d-460c-4af9-ad5b-47f555426c19', NULL, NULL, '1', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-12-23 21:19:33', '2026-04-15 12:49:40'),
('b733882c-8a6a-11f1-922a-00155d022d06', 'NSIC Checker', 'nsic-checker', NULL, '1', NULL, NULL, NULL, NULL, '', NULL, '2026-07-28 15:27:16', '2026-07-28 15:27:16'),
('c00d9d5b-8a6a-11f1-922a-00155d022d06', 'NSIC Maker', 'nsic-maker', NULL, '1', NULL, NULL, NULL, NULL, '', NULL, '2026-07-28 15:27:31', '2026-07-28 15:27:31')
ON DUPLICATE KEY UPDATE
`name` = VALUES(`name`),
`slug` = VALUES(`slug`),
`description` = VALUES(`description`),
`status` = VALUES(`status`),
`department_id` = VALUES(`department_id`),
`is_custom` = VALUES(`is_custom`),
`is_organizer` = VALUES(`is_organizer`),
`can_view_workshop` = VALUES(`can_view_workshop`),
`created_by` = VALUES(`created_by`),
`updated_by` = VALUES(`updated_by`),
`created_at` = VALUES(`created_at`),
`updated_at` = VALUES(`updated_at`);

-- ---------------------------------------------------------------------
-- STEP 4a: ROLE-PERMISSION MAPPINGS -- role_permissions (36 rows)
-- (this is the table the live login/ACL session is built from)
-- ---------------------------------------------------------------------
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
('547a585d-8e0d-11f0-81a4-00155d022d06', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('9f495362-2497-452e-991c-2d2b83872242', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a1b2559e-1436-4231-9934-4f13446410bf'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a1b255c3-915e-4687-93f0-9ee408137c4e'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a1b255e7-d12d-41a0-aeb3-3aca64292fc4'),
('9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a1b25606-49f0-4ff8-8bc3-6c085c00eb16'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', '9fada28a-467b-40d4-b775-31c12083e2dc'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', '9fada2a6-bb06-4600-8d85-6c117d7ee62c'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', '9fb76d5e-f2ae-4d59-99e6-fd54d430ab6f'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', '9fb76d88-24a4-430c-a8cb-be2f1e3f02b3'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a060621f-ef43-4ce8-be1f-41c7d36dcf9a'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a0ab3a65-3634-4680-980e-95d5dbbcf1d1'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a0ab3a96-5707-40dd-9a14-712738b6dbd7'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a1b2559e-1436-4231-9934-4f13446410bf'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a1b255e7-d12d-41a0-aeb3-3aca64292fc4'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a1b25606-49f0-4ff8-8bc3-6c085c00eb16'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a1c67eaa-e79b-4ed7-ac86-83770e388626'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a2474e30-7849-4ee0-a4a1-42f3ec8d28c5'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a247beec-4875-464f-9da9-fc3be93150a0'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a24d382c-d8b2-47d1-8f62-fcf2840b15da'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a24d3fc4-cbee-4896-81ff-fb18bbdc580d'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a24d6329-669e-472c-a920-122e436b7ae6'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a24da36c-1067-42f2-b191-a77f524ebde5'),
('9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a24da39e-d289-4537-9c37-2150237155a4'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', '9fada28a-467b-40d4-b775-31c12083e2dc'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', '9fada2a6-bb06-4600-8d85-6c117d7ee62c'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', 'a060621f-ef43-4ce8-be1f-41c7d36dcf9a'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', 'a0ab3a65-3634-4680-980e-95d5dbbcf1d1'),
('9fefc9ed-1519-4ddd-b314-2743333ab25e', 'a0ab3a96-5707-40dd-9a14-712738b6dbd7'),
('a0a9a624-c536-4989-8db9-0bacec0364bb', 'a0606255-fd84-4257-bef2-e382f14986d8'),
('b733882c-8a6a-11f1-922a-00155d022d06', 'a25db588-8185-4eb2-9d51-8d5f1836e3e0'),
('c00d9d5b-8a6a-11f1-922a-00155d022d06', 'a25db4bf-8396-4209-ae2b-81e5ddd66029'),
('c00d9d5b-8a6a-11f1-922a-00155d022d06', 'a25db588-8185-4eb2-9d51-8d5f1836e3e0')
ON DUPLICATE KEY UPDATE
`role_id` = `role_id`;

-- ---------------------------------------------------------------------
-- STEP 4b: ROLE-PERMISSION MAPPINGS -- permission_role_mapping (50 rows)
-- (this table drives the admin "roles for this permission" screen)
-- ---------------------------------------------------------------------
INSERT INTO `permission_role_mapping` (`permission_id`, `role_id`) VALUES
('9fada28a-467b-40d4-b775-31c12083e2dc', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('9fada2a6-bb06-4600-8d85-6c117d7ee62c', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('9fb76d5e-f2ae-4d59-99e6-fd54d430ab6f', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('9fb76d88-24a4-430c-a8cb-be2f1e3f02b3', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a060621f-ef43-4ce8-be1f-41c7d36dcf9a', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a060621f-ef43-4ce8-be1f-41c7d36dcf9a', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a0606255-fd84-4257-bef2-e382f14986d8', '547a585d-8e0d-11f0-81a4-00155d022d06'),
('a0606255-fd84-4257-bef2-e382f14986d8', '99bf20e7-3ad0-4423-836a-a1ea6929e704'),
('a0606255-fd84-4257-bef2-e382f14986d8', '9f495362-2497-452e-991c-2d2b83872242'),
('a0606255-fd84-4257-bef2-e382f14986d8', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a0606255-fd84-4257-bef2-e382f14986d8', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a0606255-fd84-4257-bef2-e382f14986d8', '9fefc9ed-1519-4ddd-b314-2743333ab25e'),
('a0606255-fd84-4257-bef2-e382f14986d8', 'a08ea766-4ff4-4e4f-8115-74af7100b667'),
('a0606255-fd84-4257-bef2-e382f14986d8', 'a0a9a624-c536-4989-8db9-0bacec0364bb'),
('a0ab3a65-3634-4680-980e-95d5dbbcf1d1', '99bf20e7-3ad0-4423-836a-a1ea6929e704'),
('a0ab3a65-3634-4680-980e-95d5dbbcf1d1', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a0ab3a65-3634-4680-980e-95d5dbbcf1d1', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a0ab3a65-3634-4680-980e-95d5dbbcf1d1', '9fefc9ed-1519-4ddd-b314-2743333ab25e'),
('a0ab3a96-5707-40dd-9a14-712738b6dbd7', '99bf20e7-3ad0-4423-836a-a1ea6929e704'),
('a0ab3a96-5707-40dd-9a14-712738b6dbd7', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a0ab3a96-5707-40dd-9a14-712738b6dbd7', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a0ab3a96-5707-40dd-9a14-712738b6dbd7', '9fefc9ed-1519-4ddd-b314-2743333ab25e'),
('a0e13539-0836-416d-a360-17774c7b6083', '99bf20e7-3ad0-4423-836a-a1ea6929e704'),
('a0e13539-0836-416d-a360-17774c7b6083', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a0e13539-0836-416d-a360-17774c7b6083', 'a0a9a624-c536-4989-8db9-0bacec0364bb'),
('a1b2559e-1436-4231-9934-4f13446410bf', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a1b2559e-1436-4231-9934-4f13446410bf', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1b255c3-915e-4687-93f0-9ee408137c4e', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a1b255c3-915e-4687-93f0-9ee408137c4e', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1b255e7-d12d-41a0-aeb3-3aca64292fc4', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a1b255e7-d12d-41a0-aeb3-3aca64292fc4', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1b25606-49f0-4ff8-8bc3-6c085c00eb16', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a1b25606-49f0-4ff8-8bc3-6c085c00eb16', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1c67d69-dd31-4aad-b751-3aa550e03972', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1c67da1-fce0-46d4-bc5e-81fdddb17e2c', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1c67e17-a12e-4ebc-a488-8276e351d81b', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1c67e5b-ce7c-487a-91ff-f16cfce2e264', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a1c67eaa-e79b-4ed7-ac86-83770e388626', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a2474e30-7849-4ee0-a4a1-42f3ec8d28c5', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'),
('a2474e30-7849-4ee0-a4a1-42f3ec8d28c5', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a247beec-4875-464f-9da9-fc3be93150a0', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a247bf23-1feb-46cd-8bf1-e671836a6fad', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a24d382c-d8b2-47d1-8f62-fcf2840b15da', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a24d3fc4-cbee-4896-81ff-fb18bbdc580d', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a24d6329-669e-472c-a920-122e436b7ae6', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a24da36c-1067-42f2-b191-a77f524ebde5', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a24da39e-d289-4537-9c37-2150237155a4', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'),
('a25db4bf-8396-4209-ae2b-81e5ddd66029', 'c00d9d5b-8a6a-11f1-922a-00155d022d06'),
('a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'b733882c-8a6a-11f1-922a-00155d022d06'),
('a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'c00d9d5b-8a6a-11f1-922a-00155d022d06')
ON DUPLICATE KEY UPDATE
`permission_id` = `permission_id`;

COMMIT;


-- =====================================================================
-- PART 2: VALIDATION QUERIES (run against the TARGET database after
-- executing the script above)
-- =====================================================================

-- 2a. Row counts must equal: modules=18, permissions=28, roles=10 (or
--     more, if the target already had some of these 10 roles for other
--     reasons), role_permissions>=36, permission_role_mapping>=50.
SELECT 'modules' AS tbl, COUNT(*) AS cnt FROM modules WHERE id IN (
    'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8','a1accda0-af9e-4110-a0c2-e645c0715aa0','a1b25554-79d9-445a-9fca-80d6ddb897ea',
    'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7','a25db194-86fe-4b81-b4e2-cd7e4dbd8001','a25db211-d409-4cde-bc6e-ee3ad3dee86b',
    '9fada197-2de3-404d-9a0a-14fdcb2e2eae','a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3','a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9',
    '9fb76cd8-7f35-4ce6-8a4e-bced614ed6db','a1c67b7a-f62e-4288-b041-28680641ba7f','a1c67c35-d019-4f8f-8e5d-d9163567a254',
    'a2472cdf-1568-477a-80ee-78066ab146ad','a2472d62-4663-4673-897f-8ab7f26d7f0d','a2472db8-bf19-4bdb-820e-4fe02686e7ef','a2472e03-7884-4221-b125-fa6fda7ab9b6',
    '9fada167-b216-43ff-909e-abe2c41a574e','a0606186-f978-46e5-b1fb-c5359e156424'
)
UNION ALL
SELECT 'permissions', COUNT(*) FROM permissions WHERE module_id IN (
    'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8','a1accda0-af9e-4110-a0c2-e645c0715aa0','a1b25554-79d9-445a-9fca-80d6ddb897ea',
    'a25db194-86fe-4b81-b4e2-cd7e4dbd8001','a25db211-d409-4cde-bc6e-ee3ad3dee86b',
    '9fada197-2de3-404d-9a0a-14fdcb2e2eae','a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3','a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9',
    'a1c67b7a-f62e-4288-b041-28680641ba7f','a1c67c35-d019-4f8f-8e5d-d9163567a254',
    'a2472d62-4663-4673-897f-8ab7f26d7f0d','a2472db8-bf19-4bdb-820e-4fe02686e7ef','a2472e03-7884-4221-b125-fa6fda7ab9b6'
)
UNION ALL
SELECT 'roles', COUNT(*) FROM roles WHERE id IN (
    '547a585d-8e0d-11f0-81a4-00155d022d06','99bf20e7-3ad0-4423-836a-a1ea6929e704','9f495362-2497-452e-991c-2d2b83872242',
    '9f8d4d2c-d67b-4816-a3be-2cf7de046a54','9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58','9fefc9ed-1519-4ddd-b314-2743333ab25e',
    'a08ea766-4ff4-4e4f-8115-74af7100b667','a0a9a624-c536-4989-8db9-0bacec0364bb','b733882c-8a6a-11f1-922a-00155d022d06','c00d9d5b-8a6a-11f1-922a-00155d022d06'
)
UNION ALL
SELECT 'role_permissions', COUNT(*) FROM role_permissions WHERE permission_id IN (
    SELECT id FROM permissions WHERE module_id IN (
        'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8','a1accda0-af9e-4110-a0c2-e645c0715aa0','a1b25554-79d9-445a-9fca-80d6ddb897ea',
        'a25db194-86fe-4b81-b4e2-cd7e4dbd8001','a25db211-d409-4cde-bc6e-ee3ad3dee86b',
        '9fada197-2de3-404d-9a0a-14fdcb2e2eae','a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3','a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9',
        'a1c67b7a-f62e-4288-b041-28680641ba7f','a1c67c35-d019-4f8f-8e5d-d9163567a254',
        'a2472d62-4663-4673-897f-8ab7f26d7f0d','a2472db8-bf19-4bdb-820e-4fe02686e7ef','a2472e03-7884-4221-b125-fa6fda7ab9b6'
    )
)
UNION ALL
SELECT 'permission_role_mapping', COUNT(*) FROM permission_role_mapping WHERE permission_id IN (
    SELECT id FROM permissions WHERE module_id IN (
        'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8','a1accda0-af9e-4110-a0c2-e645c0715aa0','a1b25554-79d9-445a-9fca-80d6ddb897ea',
        'a25db194-86fe-4b81-b4e2-cd7e4dbd8001','a25db211-d409-4cde-bc6e-ee3ad3dee86b',
        '9fada197-2de3-404d-9a0a-14fdcb2e2eae','a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3','a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9',
        'a1c67b7a-f62e-4288-b041-28680641ba7f','a1c67c35-d019-4f8f-8e5d-d9163567a254',
        'a2472d62-4663-4673-897f-8ab7f26d7f0d','a2472db8-bf19-4bdb-820e-4fe02686e7ef','a2472e03-7884-4221-b125-fa6fda7ab9b6'
    )
);

-- 2b. Referential sanity: every migrated permission's module_id must
--     resolve to a module that exists in the target (0 rows = pass).
SELECT p.id, p.slug, p.module_id
FROM permissions p
LEFT JOIN modules m ON m.id = p.module_id
WHERE p.id IN (
    'a1c67da1-fce0-46d4-bc5e-81fdddb17e2c','a1c67e17-a12e-4ebc-a488-8276e351d81b','a1c67e5b-ce7c-487a-91ff-f16cfce2e264',
    'a0e13539-0836-416d-a360-17774c7b6083','a060621f-ef43-4ce8-be1f-41c7d36dcf9a','a0606255-fd84-4257-bef2-e382f14986d8',
    'a0ab3a65-3634-4680-980e-95d5dbbcf1d1','a0ab3a96-5707-40dd-9a14-712738b6dbd7','a1b2559e-1436-4231-9934-4f13446410bf',
    'a1b255c3-915e-4687-93f0-9ee408137c4e','a1b255e7-d12d-41a0-aeb3-3aca64292fc4','a1b25606-49f0-4ff8-8bc3-6c085c00eb16',
    'a2474e30-7849-4ee0-a4a1-42f3ec8d28c5','9fada2a6-bb06-4600-8d85-6c117d7ee62c','a1c67d69-dd31-4aad-b751-3aa550e03972',
    'a247beec-4875-464f-9da9-fc3be93150a0','a247bf23-1feb-46cd-8bf1-e671836a6fad','9fada28a-467b-40d4-b775-31c12083e2dc',
    '9fb76d88-24a4-430c-a8cb-be2f1e3f02b3','a1c67eaa-e79b-4ed7-ac86-83770e388626','9fb76d5e-f2ae-4d59-99e6-fd54d430ab6f',
    'a24d382c-d8b2-47d1-8f62-fcf2840b15da','a24da36c-1067-42f2-b191-a77f524ebde5','a24da39e-d289-4537-9c37-2150237155a4',
    'a24d3fc4-cbee-4896-81ff-fb18bbdc580d','a24d6329-669e-472c-a920-122e436b7ae6','a25db4bf-8396-4209-ae2b-81e5ddd66029',
    'a25db588-8185-4eb2-9d51-8d5f1836e3e0'
) AND m.id IS NULL;

-- 2c. Referential sanity: every migrated role_permissions /
--     permission_role_mapping row must resolve to an existing role AND
--     an existing permission in the target (0 rows = pass).
SELECT rp.role_id, rp.permission_id
FROM role_permissions rp
LEFT JOIN roles r ON r.id = rp.role_id
LEFT JOIN permissions p ON p.id = rp.permission_id
WHERE rp.permission_id IN (
    SELECT id FROM permissions WHERE module_id IN (
        'a0e133a5-1ec4-4bdc-8bd7-9643fe5e2da8','a1accda0-af9e-4110-a0c2-e645c0715aa0','a1b25554-79d9-445a-9fca-80d6ddb897ea',
        'a25db194-86fe-4b81-b4e2-cd7e4dbd8001','a25db211-d409-4cde-bc6e-ee3ad3dee86b',
        '9fada197-2de3-404d-9a0a-14fdcb2e2eae','a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3','a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9',
        'a1c67b7a-f62e-4288-b041-28680641ba7f','a1c67c35-d019-4f8f-8e5d-d9163567a254',
        'a2472d62-4663-4673-897f-8ab7f26d7f0d','a2472db8-bf19-4bdb-820e-4fe02686e7ef','a2472e03-7884-4221-b125-fa6fda7ab9b6'
    )
) AND (r.id IS NULL OR p.id IS NULL);

-- 2d. Spot-check: reproduce the full permission -> module -> role tree
--     for one feature (Fund Distribution) exactly as it should appear
--     in the target admin UI.
SELECT m.name AS module_name, p.name AS permission_name, p.slug, r.name AS role_name
FROM permissions p
JOIN modules m ON m.id = p.module_id
LEFT JOIN role_permissions rp ON rp.permission_id = p.id
LEFT JOIN roles r ON r.id = rp.role_id
WHERE p.module_id IN ('9fb76cd8-7f35-4ce6-8a4e-bced614ed6db', 'a1c67b7a-f62e-4288-b041-28680641ba7f', 'a1c67c35-d019-4f8f-8e5d-d9163567a254')
ORDER BY m.name, p.name, r.name;
