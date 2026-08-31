-- =====================================================================
-- insert_last_1_month.sql
-- Idempotent INSERT script for module/permission/role data changed in the
-- last 1 month in source database `team_portal47` (window: 2026-07-24 16:10:40 .. now).
--
-- Safe to re-run: every INSERT is guarded by WHERE NOT EXISTS against the
-- target table's primary key, so rows that already exist in the target
-- database are silently skipped instead of raising a duplicate-key error.
--
-- Order of sections follows dependency order (even though none of these
-- tables besides `roles` have DB-enforced foreign keys, the app relies on
-- these relationships, so parents are inserted before children):
--   1) role_types              (needed by roles.role_type_id - this IS an
--                              enforced FK: fk_roles_role_type)
--   2) modules                 (needed by permissions.module_id)
--   3) permissions              (needed by role_permissions / permission_role_mapping)
--   4) roles                   (needed by role_permissions / permission_role_mapping)
--   5) role_permissions        (role -> permission mapping, composite PK)
--   6) permission_role_mapping (permission -> role mapping, composite PK)
--
-- Each of sections 2-4 contains two groups:
--   (a) rows actually created/updated in the last 1 month
--   (b) older 'dependency' rows that are NOT new, but are required so the
--       foreign keys / relationships of the rows in (a) resolve correctly
--       (e.g. a permission created this month may belong to a module that
--       was created long ago; that module must exist in the target DB too).
--       These are also wrapped in WHERE NOT EXISTS, so on a target DB that
--       already has the baseline data, they are simple no-ops.
--
-- NOTE: role_permissions / permission_role_mapping have no created_at /
-- updated_at columns in the source schema, so 'last 1 month' mappings are
-- those that reference a permission or role that was itself created or
-- updated in the last 1 month.
--
-- NOTE: 2 rows were excluded from permission_role_mapping because they
-- reference role_id 'a26d6841-d2ac-4d1e-9f8c-9fa9e6c5704a', which does not
-- exist in the `roles` table in the source DB (an orphaned reference left
-- over from a deleted role, possible only because no FK is enforced there).
--
-- NOTE: the test role 'ankit test' (id 'a27bea87-53dd-4808-9d1c-abba9f39d5bf',
-- role_type 'Sub Test Q1') and all of its role_permissions /
-- permission_role_mapping rows have been intentionally excluded from this
-- script, at request. As a result, the role_type 'Sub Test Q1'
-- (id 'a27c1bd2-cee6-4b4f-8fd7-002d9bad778c') and the 10 permissions that
-- were only pulled in as FK dependencies for that role's grants (User
-- Create/Update/View, Fund-allocation-view/create/edit/delete,
-- View-my-profile, View-profile-history, Fund Allocation View) are also
-- omitted, since nothing else in this dataset still depends on them.
-- =====================================================================

START TRANSACTION;

-- ---------------------------------------------------------------------
-- 1) role_types (dependency for roles.role_type_id, enforced FK)
-- ---------------------------------------------------------------------
INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bee500-96e0-11f1-922a-00155d022d06', 'Buyer Network Participant (BNP)', 'bnp', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bee500-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bee76e-96e0-11f1-922a-00155d022d06', 'Administrator', 'administrator', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bee76e-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bee867-96e0-11f1-922a-00155d022d06', 'Seller Network Participant (SNP)', 'seller-network-participant-snp', 1, '2026-08-13 11:57:37', '2026-08-24 14:00:05'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bee867-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bee8fe-96e0-11f1-922a-00155d022d06', 'ONDC Admin', 'ondc-admin', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bee8fe-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bee986-96e0-11f1-922a-00155d022d06', 'NSIC', 'nsic', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bee986-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13beeb2b-96e0-11f1-922a-00155d022d06', 'NSIC Finance', 'nsic-finance', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13beeb2b-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13beed1c-96e0-11f1-922a-00155d022d06', 'Logistics Service Provider (LSP)', 'lsp', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13beed1c-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13beee21-96e0-11f1-922a-00155d022d06', 'MSME', 'msme', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13beee21-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bef12b-96e0-11f1-922a-00155d022d06', 'NSIC Checker', 'nsic-checker', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bef12b-96e0-11f1-922a-00155d022d06');

INSERT INTO `role_types` (id, name, slug, status, created_at, updated_at)
SELECT '13bef1f6-96e0-11f1-922a-00155d022d06', 'NSIC Maker', 'nsic-maker', 1, '2026-08-13 11:57:37', '2026-08-13 11:57:37'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_types` WHERE id = '13bef1f6-96e0-11f1-922a-00155d022d06');

-- ---------------------------------------------------------------------
-- 2) modules
-- ---------------------------------------------------------------------
-- (a) modules created/updated in the last 1 month
INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, NULL, 'AI Cataloguing Claim', 20, 'ai-cataloguing-claim', 'ai-cat', NULL, 'fa fa-cog', NULL, 1, NULL, 1, '2026-07-28 15:30:31', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a25db194-86fe-4b81-b4e2-cd7e4dbd8001', 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, 'File a Claim', 22, 'file-a-claim', 'file-ai-cataloguing-claim', NULL, 'fa fa-cog', NULL, 1, NULL, 1, '2026-07-28 15:32:29', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a25db194-86fe-4b81-b4e2-cd7e4dbd8001');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a25db211-d409-4cde-bc6e-ee3ad3dee86b', 'a25db0e1-160c-4e2a-9b52-e4dd7b5895a7', NULL, 'View Your Claim', 24, 'view-your-claim', 'ai-cataloguing-claim', NULL, 'fa fa-cog', NULL, 1, NULL, 1, '2026-07-28 15:33:51', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a25db211-d409-4cde-bc6e-ee3ad3dee86b');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a2797f36-15c0-4909-aaa5-6a7a81155411', '9f958e02-c2eb-4fd1-b0ac-b2a4262abfe3', NULL, 'Failed MSME List', 6, 'failed-msme-list', 'failed-msme-list', NULL, 'doc.svg', NULL, 1, NULL, 1, '2026-08-11 11:14:49', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a2797f36-15c0-4909-aaa5-6a7a81155411');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a27bc89c-7269-4268-bb52-98fb937369e5', '9a8fcbb9-e1b1-4554-914c-13cbada0bc25', NULL, 'Role Types', 3, 'role-types', 'role-types', NULL, NULL, NULL, 1, NULL, 1, '2026-08-12 14:31:44', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a27bc89c-7269-4268-bb52-98fb937369e5');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a285abd2-90dc-4c0c-b195-fd2914610073', '9a8fcbb9-e1b1-4554-914c-13cbada0bc25', NULL, 'User Activity Logs', 4, 'user-logs', 'user-logs', NULL, NULL, NULL, 1, NULL, 1, '2026-08-17 12:29:31', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a285abd2-90dc-4c0c-b195-fd2914610073');

-- (b) older modules required as FK dependency for permissions below
INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT '9a8fcaf3-50a6-4c15-be29-069d34e10002', NULL, 1, 'Dashboard', 10, 'dashboard', 'dashboard', NULL, 'home.svg', NULL, 1, NULL, 1, '2023-11-08 11:39:19', '2026-01-27 14:56:28', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = '9a8fcaf3-50a6-4c15-be29-069d34e10002');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT '9a8fcdc4-9304-4a9e-ad15-fa17b15e9891', '9a8fcd84-7e0a-426a-9b60-08c1a7f855cd', NULL, 'Permissions', 2, 'permissions', 'permissions', NULL, 'permissions', NULL, 1, NULL, 1, '2023-11-08 11:47:12', '2026-04-23 17:02:07', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = '9a8fcdc4-9304-4a9e-ad15-fa17b15e9891');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT '9f66feff-4c16-4f3a-9f04-2d325a84c97d', '9f958e02-c2eb-4fd1-b0ac-b2a4262abfe3', NULL, 'Open MSE', 3, 'open-mse', 'open-msme', NULL, 'dashboard.svg', NULL, 1, NULL, 1, '2025-07-16 11:55:36', '2026-01-27 15:07:41', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = '9f66feff-4c16-4f3a-9f04-2d325a84c97d');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT '9f959020-12bd-49db-91aa-82afb32436c1', '9f958e02-c2eb-4fd1-b0ac-b2a4262abfe3', NULL, 'Direct Selection By MSE', 3, 'direct-selection-by-mse', 'msme-chossen-me', NULL, 'dashboard', NULL, 1, NULL, 1, '2025-08-08 15:29:29', '2026-01-27 15:07:54', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = '9f959020-12bd-49db-91aa-82afb32436c1');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0c9448c-4ac1-4254-969d-ec6112626537', 'a0c91dfd-d3da-4ad3-96d9-1e7137642c36', NULL, 'File a Claim', 0, 'file-a-claim', 'file-demand-generation-incentive-claim', NULL, 'fa fa-cog', NULL, 1, NULL, 1, '2026-01-08 14:33:09', NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', NULL
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0c9448c-4ac1-4254-969d-ec6112626537');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0cf9fb8-c34b-4137-ab92-b64fd994ad1d', '9f958e02-c2eb-4fd1-b0ac-b2a4262abfe3', NULL, 'MSE To Be Validated', 4, 'mse-to-be-validated', 'mse-to-be-validated', NULL, 'mse-to-be-validated', NULL, 1, NULL, 1, '2026-01-11 18:23:03', '2026-01-27 15:08:16', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0cf9fb8-c34b-4137-ab92-b64fd994ad1d');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0cfa0aa-313e-4852-bf4b-b72bc0beee02', '9f958e02-c2eb-4fd1-b0ac-b2a4262abfe3', NULL, 'Validated MSE', 5, 'validated-mse', 'validated-mse', NULL, 'validated-mse', NULL, 1, NULL, 1, '2026-01-11 18:25:41', '2026-01-27 15:08:27', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0cfa0aa-313e-4852-bf4b-b72bc0beee02');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0d77223-e690-4e25-ab06-977d68c436b5', 'a0d77178-cc1c-4fe3-9905-7cea942c0969', NULL, 'Claim for account and managment', 1, 'claim-for-account-and-managment', 'accounts-created', NULL, 'doc.svg', NULL, 1, NULL, 1, '2026-01-15 15:42:13', '2026-02-17 18:34:23', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0d77223-e690-4e25-ab06-977d68c436b5');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0d772a8-3d8a-44ca-b37c-26d5c97f7f9b', 'a0d77178-cc1c-4fe3-9905-7cea942c0969', NULL, 'Claim for Packaging', 2, 'claim-for-packaging', 'claim-for-packaging', NULL, 'doc.svg', NULL, 1, NULL, 1, '2026-01-15 15:43:40', '2026-02-24 12:12:53', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0d772a8-3d8a-44ca-b37c-26d5c97f7f9b');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT '9a8fcbfa-4dd7-4526-abd0-0b7095cb2646', '9a8fcbb9-e1b1-4554-914c-13cbada0bc25', NULL, 'Users', 2, 'users', 'users', NULL, 'users', NULL, 1, NULL, 1, '2023-11-08 11:42:11', '2026-01-27 17:41:12', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = '9a8fcbfa-4dd7-4526-abd0-0b7095cb2646');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a0ab4ce0-9fcd-47af-b678-384eaa6d8dd6', NULL, NULL, 'My Profile', 17, 'my-profile', 'msme-my-profile', NULL, 'document.svg', NULL, 1, NULL, 1, '2025-12-24 17:01:36', '2026-01-27 22:48:12', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a0ab4ce0-9fcd-47af-b678-384eaa6d8dd6');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, 'New Allocation', 30, 'new-allocation', 'fund-allocation/create', NULL, 'document', NULL, 1, NULL, 1, '2026-05-14 11:27:27', '2026-07-17 17:26:53', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a1c67a21-f6df-4e98-86eb-bfe9fb83a6e3');

INSERT INTO `modules` (id, parent_id, module_type, name, sort_order, slug, url, second_url, icon, color, type, description, status, created_at, updated_at, created_by, updated_by)
SELECT 'a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9', '9fada197-2de3-404d-9a0a-14fdcb2e2eae', NULL, 'All Allocations', 30, 'all-allocations', 'fund-allocations', NULL, 'document', NULL, 1, NULL, 1, '2026-05-14 11:28:41', '2026-07-17 17:32:14', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `modules` WHERE id = 'a1c67a93-f0a5-4e5f-9688-fea4dc10b9a9');


-- ---------------------------------------------------------------------
-- 3) permissions
-- ---------------------------------------------------------------------
-- (a) permissions created/updated in the last 1 month
INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', 'Dashboard', 'dashboard', '', '9a8fcaf3-50a6-4c15-be29-069d34e10002', NULL, 1, '2023-11-08 12:03:54', '2026-08-06 11:29:56'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b', 'Permission-View', 'permission-view', '', '9a8fcdc4-9304-4a9e-ad15-fa17b15e9891', NULL, 1, '2023-11-08 12:10:37', '2026-08-05 11:39:46'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', 'Dashboard View', 'dashboard-view', 'dash', '9a8fcaf3-50a6-4c15-be29-069d34e10002', 1, 0, '2023-12-21 12:29:22', '2026-08-05 12:47:36'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a03613b2-c511-4c9d-83b5-807ae6eb9c11', 'File A Claim Create View', 'file-a-claim-create-view', 'File A Claim Create View', 'a0c9448c-4ac1-4254-969d-ec6112626537', NULL, 1, '2025-10-27 10:29:51', '2026-08-06 11:24:38'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a03613b2-c511-4c9d-83b5-807ae6eb9c11');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6', 'Update-bppid-open', 'update-bppid-open', '', '9f66feff-4c16-4f3a-9f04-2d325a84c97d', NULL, 1, '2026-07-22 13:50:03', '2026-08-24 13:10:11'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6', 'Update-bppid-direct', 'update-bppid-direct', '', '9f959020-12bd-49db-91aa-82afb32436c1', NULL, 1, '2026-07-22 14:01:38', '2026-08-24 13:51:45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a251844a-c100-4b0a-a978-feec040e3a89', 'Update-bppid-validated', 'update-bppid-validated', '', 'a0cfa0aa-313e-4852-bf4b-b72bc0beee02', NULL, 1, '2026-07-22 14:15:55', '2026-08-13 10:44:20'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a251844a-c100-4b0a-a978-feec040e3a89');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a25db4bf-8396-4209-ae2b-81e5ddd66029', 'File A Claim View', 'file-a-claim-view', '', 'a25db194-86fe-4b81-b4e2-cd7e4dbd8001', NULL, 1, '2026-07-28 15:41:21', '2026-07-28 15:57:18'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a25db4bf-8396-4209-ae2b-81e5ddd66029');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'AI Cataloguing Claim View', 'ai-cataloguing-claim-view', '', 'a25db211-d409-4cde-bc6e-ee3ad3dee86b', NULL, 1, '2026-07-28 15:43:32', '2026-07-28 15:45:35'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a25db588-8185-4eb2-9d51-8d5f1836e3e0');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a279809b-c57e-4552-9b3a-be2ed0986709', 'Failed Msme List View', 'failed-msme-list-view', 'Failed Msme List View', 'a2797f36-15c0-4909-aaa5-6a7a81155411', NULL, 1, '2026-08-11 11:18:44', '2026-08-11 11:18:44'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a279809b-c57e-4552-9b3a-be2ed0986709');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a279e2db-d1ea-46cd-a70a-f5bd5e309bb1', 'Claim Account Create', 'claim-account-create', 'Claim Account Create', 'a0d77223-e690-4e25-ab06-977d68c436b5', NULL, 1, '2026-08-11 15:53:27', '2026-08-11 15:53:27'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a279e2db-d1ea-46cd-a70a-f5bd5e309bb1');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a279e4a2-6084-42cc-a4fe-fbbdbee24661', 'Claim Packaging Create', 'claim-packaging-create', 'Claim Packaging Create', 'a0d772a8-3d8a-44ca-b37c-26d5c97f7f9b', NULL, 1, '2026-08-11 15:58:25', '2026-08-11 15:58:25'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a279e4a2-6084-42cc-a4fe-fbbdbee24661');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27bc89d-003f-43d7-a224-d475719ed8b8', 'Role Type View', 'role-type-view', 'Role Type View permission for the Role Type module.', 'a27bc89c-7269-4268-bb52-98fb937369e5', NULL, 1, '2026-08-12 14:31:44', '2026-08-12 15:04:51'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27bc89d-003f-43d7-a224-d475719ed8b8');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27bc89d-062a-469c-9b70-355130a758e3', 'Role Type Create', 'role-type-create', 'Role Type Create permission for the Role Type module.', 'a27bc89c-7269-4268-bb52-98fb937369e5', NULL, 1, '2026-08-12 14:31:44', '2026-08-12 14:31:44'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27bc89d-062a-469c-9b70-355130a758e3');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c', 'Role Type Update', 'role-type-update', 'Role Type Update permission for the Role Type module.', 'a27bc89c-7269-4268-bb52-98fb937369e5', NULL, 1, '2026-08-12 14:31:44', '2026-08-12 14:31:44'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27bc89d-0fb1-4006-9467-99499994cb0f', 'Role Type Permission View', 'role-type-permission-view', 'Role Type Permission View permission for the Role Type module.', 'a27bc89c-7269-4268-bb52-98fb937369e5', NULL, 1, '2026-08-12 14:31:44', '2026-08-12 14:31:44'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27bc89d-0fb1-4006-9467-99499994cb0f');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1', 'MSE To Be Validated Update', 'mse-to-be-validated-update', 'MSE To Be Validated Update', 'a0cf9fb8-c34b-4137-ab92-b64fd994ad1d', NULL, 1, '2026-08-13 10:03:58', '2026-08-13 10:03:58'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a27d7977-69be-4948-bcc2-0c44235941b8', 'Update-bppid-validate-mse', 'update-bppid-validate-mse', 'Update-bppid-validate-mse', 'a0cfa0aa-313e-4852-bf4b-b72bc0beee02', NULL, 1, '2026-08-13 10:42:04', '2026-08-13 10:42:04'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a27d7977-69be-4948-bcc2-0c44235941b8');

INSERT INTO `permissions` (id, name, slug, description, module_id, show_to_custom_user, status, created_at, updated_at)
SELECT 'a285abd3-fccb-4dfc-ac04-c431e8a1657d', 'User Log View', 'user-log-view', 'User Log View permission for the User Activity Logs module.', 'a285abd2-90dc-4c0c-b195-fd2914610073', NULL, 1, '2026-08-17 12:29:31', '2026-08-17 13:30:32'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permissions` WHERE id = 'a285abd3-fccb-4dfc-ac04-c431e8a1657d');

-- (b) older permissions required as FK dependency for role_permissions / permission_role_mapping below
-- NOTE: previously included 10 permissions here (User Create/Update/View,
-- Fund-allocation-view/create/edit/delete, View-my-profile, View-profile-history,
-- Fund Allocation View) that existed only to satisfy foreign keys for the
-- 'ankit test' role's mappings. Since that role and its mappings have been
-- removed below, none of those permissions are needed as dependencies here.


-- ---------------------------------------------------------------------
-- 4) roles
-- ---------------------------------------------------------------------
-- (a) roles created/updated in the last 1 month
INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT 'b733882c-8a6a-11f1-922a-00155d022d06', 'NSIC Checker', 'nsic-checker', NULL, 1, NULL, '13bef12b-96e0-11f1-922a-00155d022d06', NULL, NULL, NULL, '', NULL, '2026-07-28 15:27:16', '2026-07-28 15:27:16'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = 'b733882c-8a6a-11f1-922a-00155d022d06');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT 'c00d9d5b-8a6a-11f1-922a-00155d022d06', 'NSIC Maker', 'nsic-maker', NULL, 1, NULL, '13bef1f6-96e0-11f1-922a-00155d022d06', NULL, NULL, NULL, '', NULL, '2026-07-28 15:27:31', '2026-07-28 15:27:31'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = 'c00d9d5b-8a6a-11f1-922a-00155d022d06');

-- (b) older roles required as FK dependency for role_permissions / permission_role_mapping below
INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '547a585d-8e0d-11f0-81a4-00155d022d06', 'Buyer Network Participant (BNP)', 'bnp', 'aassaas', 1, '99f3541c-a284-427d-b77a-3ff2062e8a20', '13bee500-96e0-11f1-922a-00155d022d06', NULL, NULL, 1, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-07-01 17:56:43', '2025-09-26 12:12:50'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '547a585d-8e0d-11f0-81a4-00155d022d06');

-- NOTE: source row has an invalid zero created_at ('0000-00-00 00:00:00').
-- This role is expected to already exist in any real environment (it is
-- the baseline 'Administrator' role), so the NOT EXISTS guard should skip
-- this INSERT in practice. If it ever does run, it may fail under strict
-- SQL modes (NO_ZERO_DATE); in that case, create the Administrator role
-- manually first, then re-run this script.
INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', 'Administrator', 'administrator', NULL, 1, NULL, '13bee76e-96e0-11f1-922a-00155d022d06', NULL, NULL, NULL, '', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '0000-00-00 00:00:00', '2023-12-20 16:13:09'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f495362-2497-452e-991c-2d2b83872242', 'Seller Network Participant (SNP)', 'snp', NULL, 1, '99f3541c-a284-427d-b77a-3ff2062e8a20', '13bee867-96e0-11f1-922a-00155d022d06', NULL, NULL, 1, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-07-01 17:56:43', '2025-07-01 18:32:09'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'ONDC Admin', 'ondc-admin', NULL, 1, '57b172e6-efef-11ee-8177-00155d022d06', '13bee8fe-96e0-11f1-922a-00155d022d06', NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-04 12:55:39', '2025-08-04 12:55:57'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'NSIC', 'nsic', NULL, 1, '9bf41ad0-acc9-4072-b50e-7fc66a5a0d5a', '13bee986-96e0-11f1-922a-00155d022d06', NULL, 1, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-04 12:56:36', '2025-08-04 12:56:36'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f913336-e256-4eaa-8d8b-5ba364bc9526', 'NSIC PMU', 'nsic-pmu', NULL, 0, '9f9132cc-004c-4351-9a42-dbb8905ed266', NULL, NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-06 11:26:23', '2025-08-06 11:26:23'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f913336-e256-4eaa-8d8b-5ba364bc9526');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f913350-9e9b-4fbd-b07c-9bb195a46a1e', 'NSIC Business', 'nsic-business', NULL, 0, '9f913310-3533-4448-aef8-3be245748cef', NULL, NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-06 11:26:39', '2025-09-26 12:15:26'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f913350-9e9b-4fbd-b07c-9bb195a46a1e');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9f91336d-d391-42cd-842c-63017bb48de0', 'NSIC Finance', 'nsic-finance', NULL, 1, '9f913324-2357-4220-a25c-19ddb2c1032f', '13beeb2b-96e0-11f1-922a-00155d022d06', NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-08-06 11:26:59', '2025-09-26 12:15:30'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9f91336d-d391-42cd-842c-63017bb48de0');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9fefc9ed-1519-4ddd-b314-2743333ab25e', 'MO MSE', 'mo-mse', NULL, 0, '9bf41ad0-acc9-4072-b50e-7fc66a5a0d5a', NULL, NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-09-22 11:55:37', '2025-09-26 12:15:20'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9fefc9ed-1519-4ddd-b314-2743333ab25e');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT '9ffe00c0-efe0-48a1-835e-3de599dff33e', 'CA', 'ca', 'test ca', 0, '99f3541c-a284-427d-b77a-3ff2062e8a20', NULL, NULL, NULL, NULL, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-09-29 13:30:31', '2026-07-23 10:35:04'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = '9ffe00c0-efe0-48a1-835e-3de599dff33e');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT 'a08ea766-4ff4-4e4f-8115-74af7100b667', 'Logistics Service Provider (LSP)', 'lsp', NULL, 1, 'a08ea750-941b-4820-a97d-59ba683cda5c', '13beed1c-96e0-11f1-922a-00155d022d06', NULL, NULL, 1, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-12-10 11:15:42', '2025-12-10 11:15:42'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = 'a08ea766-4ff4-4e4f-8115-74af7100b667');

INSERT INTO `roles` (id, name, slug, description, status, department_id, role_type_id, is_custom, is_organizer, can_view_workshop, created_by, updated_by, created_at, updated_at)
SELECT 'a0a9a624-c536-4989-8db9-0bacec0364bb', 'MSME', 'msme', NULL, 1, 'a0a9a60d-460c-4af9-ad5b-47f555426c19', '13beee21-96e0-11f1-922a-00155d022d06', NULL, NULL, 1, '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '99ca7fad-9cbd-4081-abde-b7b50f9cd945', '2025-12-23 21:19:33', '2026-04-15 12:49:40'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `roles` WHERE id = 'a0a9a624-c536-4989-8db9-0bacec0364bb');


-- ---------------------------------------------------------------------
-- 5) role_permissions (role -> permission mapping)
-- ---------------------------------------------------------------------
INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '547a585d-8e0d-11f0-81a4-00155d022d06', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '547a585d-8e0d-11f0-81a4-00155d022d06' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '547a585d-8e0d-11f0-81a4-00155d022d06', 'a03613b2-c511-4c9d-83b5-807ae6eb9c11'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '547a585d-8e0d-11f0-81a4-00155d022d06' AND permission_id = 'a03613b2-c511-4c9d-83b5-807ae6eb9c11');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', 'a27bc89d-003f-43d7-a224-d475719ed8b8'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = 'a27bc89d-003f-43d7-a224-d475719ed8b8');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', 'a27bc89d-062a-469c-9b70-355130a758e3'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = 'a27bc89d-062a-469c-9b70-355130a758e3');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '99bf20e7-3ad0-4423-836a-a1ea6929e704', 'a27bc89d-0fb1-4006-9467-99499994cb0f'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704' AND permission_id = 'a27bc89d-0fb1-4006-9467-99499994cb0f');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', 'a279e4a2-6084-42cc-a4fe-fbbdbee24661'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = 'a279e4a2-6084-42cc-a4fe-fbbdbee24661');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f495362-2497-452e-991c-2d2b83872242', 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f495362-2497-452e-991c-2d2b83872242' AND permission_id = 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f8d4d2c-d67b-4816-a3be-2cf7de046a54', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f8d4d2c-d67b-4816-a3be-2cf7de046a54', 'a279809b-c57e-4552-9b3a-be2ed0986709'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54' AND permission_id = 'a279809b-c57e-4552-9b3a-be2ed0986709');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58', 'a279809b-c57e-4552-9b3a-be2ed0986709'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58' AND permission_id = 'a279809b-c57e-4552-9b3a-be2ed0986709');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f913336-e256-4eaa-8d8b-5ba364bc9526', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f913336-e256-4eaa-8d8b-5ba364bc9526' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f913336-e256-4eaa-8d8b-5ba364bc9526', '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f913336-e256-4eaa-8d8b-5ba364bc9526' AND permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f913350-9e9b-4fbd-b07c-9bb195a46a1e', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f913350-9e9b-4fbd-b07c-9bb195a46a1e' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f913350-9e9b-4fbd-b07c-9bb195a46a1e', '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f913350-9e9b-4fbd-b07c-9bb195a46a1e' AND permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f91336d-d391-42cd-842c-63017bb48de0', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f91336d-d391-42cd-842c-63017bb48de0' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9f91336d-d391-42cd-842c-63017bb48de0', '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9f91336d-d391-42cd-842c-63017bb48de0' AND permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9fefc9ed-1519-4ddd-b314-2743333ab25e', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9fefc9ed-1519-4ddd-b314-2743333ab25e' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT '9ffe00c0-efe0-48a1-835e-3de599dff33e', '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = '9ffe00c0-efe0-48a1-835e-3de599dff33e' AND permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT 'a08ea766-4ff4-4e4f-8115-74af7100b667', '9a8fd3be-6102-42ef-912c-ea0456ac0e45'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = 'a08ea766-4ff4-4e4f-8115-74af7100b667' AND permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT 'a0a9a624-c536-4989-8db9-0bacec0364bb', '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = 'a0a9a624-c536-4989-8db9-0bacec0364bb' AND permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT 'b733882c-8a6a-11f1-922a-00155d022d06', 'a25db588-8185-4eb2-9d51-8d5f1836e3e0'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = 'b733882c-8a6a-11f1-922a-00155d022d06' AND permission_id = 'a25db588-8185-4eb2-9d51-8d5f1836e3e0');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT 'c00d9d5b-8a6a-11f1-922a-00155d022d06', 'a25db4bf-8396-4209-ae2b-81e5ddd66029'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = 'c00d9d5b-8a6a-11f1-922a-00155d022d06' AND permission_id = 'a25db4bf-8396-4209-ae2b-81e5ddd66029');

INSERT INTO `role_permissions` (role_id, permission_id)
SELECT 'c00d9d5b-8a6a-11f1-922a-00155d022d06', 'a25db588-8185-4eb2-9d51-8d5f1836e3e0'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `role_permissions` WHERE role_id = 'c00d9d5b-8a6a-11f1-922a-00155d022d06' AND permission_id = 'a25db588-8185-4eb2-9d51-8d5f1836e3e0');


-- ---------------------------------------------------------------------
-- 6) permission_role_mapping (permission -> role mapping)
-- ---------------------------------------------------------------------
INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '547a585d-8e0d-11f0-81a4-00155d022d06'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '547a585d-8e0d-11f0-81a4-00155d022d06');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f913336-e256-4eaa-8d8b-5ba364bc9526'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f913336-e256-4eaa-8d8b-5ba364bc9526');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f913350-9e9b-4fbd-b07c-9bb195a46a1e'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f913350-9e9b-4fbd-b07c-9bb195a46a1e');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9f91336d-d391-42cd-842c-63017bb48de0'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9f91336d-d391-42cd-842c-63017bb48de0');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', '9fefc9ed-1519-4ddd-b314-2743333ab25e'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = '9fefc9ed-1519-4ddd-b314-2743333ab25e');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', 'a08ea766-4ff4-4e4f-8115-74af7100b667'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = 'a08ea766-4ff4-4e4f-8115-74af7100b667');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd3be-6102-42ef-912c-ea0456ac0e45', 'a0a9a624-c536-4989-8db9-0bacec0364bb'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd3be-6102-42ef-912c-ea0456ac0e45' AND role_id = 'a0a9a624-c536-4989-8db9-0bacec0364bb');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9a8fd624-a71a-4c95-98f4-6f16ac7deb0b' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f913336-e256-4eaa-8d8b-5ba364bc9526'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f913336-e256-4eaa-8d8b-5ba364bc9526');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f913350-9e9b-4fbd-b07c-9bb195a46a1e'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f913350-9e9b-4fbd-b07c-9bb195a46a1e');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9f91336d-d391-42cd-842c-63017bb48de0'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9f91336d-d391-42cd-842c-63017bb48de0');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', '9ffe00c0-efe0-48a1-835e-3de599dff33e'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = '9ffe00c0-efe0-48a1-835e-3de599dff33e');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21', 'a0a9a624-c536-4989-8db9-0bacec0364bb'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = '9ae65d2b-2c5b-43fc-9af1-8ca8f52abe21' AND role_id = 'a0a9a624-c536-4989-8db9-0bacec0364bb');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a03613b2-c511-4c9d-83b5-807ae6eb9c11', '547a585d-8e0d-11f0-81a4-00155d022d06'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a03613b2-c511-4c9d-83b5-807ae6eb9c11' AND role_id = '547a585d-8e0d-11f0-81a4-00155d022d06');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a03613b2-c511-4c9d-83b5-807ae6eb9c11', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a03613b2-c511-4c9d-83b5-807ae6eb9c11' AND role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a03613b2-c511-4c9d-83b5-807ae6eb9c11', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a03613b2-c511-4c9d-83b5-807ae6eb9c11' AND role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a2517b0b-32dd-4cb4-82fa-cbbd07d994c6' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a2517f2e-7fc2-459e-8b12-b7da7f8736e6' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a25db4bf-8396-4209-ae2b-81e5ddd66029', 'c00d9d5b-8a6a-11f1-922a-00155d022d06'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a25db4bf-8396-4209-ae2b-81e5ddd66029' AND role_id = 'c00d9d5b-8a6a-11f1-922a-00155d022d06');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'b733882c-8a6a-11f1-922a-00155d022d06'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a25db588-8185-4eb2-9d51-8d5f1836e3e0' AND role_id = 'b733882c-8a6a-11f1-922a-00155d022d06');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a25db588-8185-4eb2-9d51-8d5f1836e3e0', 'c00d9d5b-8a6a-11f1-922a-00155d022d06'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a25db588-8185-4eb2-9d51-8d5f1836e3e0' AND role_id = 'c00d9d5b-8a6a-11f1-922a-00155d022d06');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a279809b-c57e-4552-9b3a-be2ed0986709', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a279809b-c57e-4552-9b3a-be2ed0986709' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a279809b-c57e-4552-9b3a-be2ed0986709', '9f8d4d2c-d67b-4816-a3be-2cf7de046a54'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a279809b-c57e-4552-9b3a-be2ed0986709' AND role_id = '9f8d4d2c-d67b-4816-a3be-2cf7de046a54');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a279809b-c57e-4552-9b3a-be2ed0986709', '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a279809b-c57e-4552-9b3a-be2ed0986709' AND role_id = '9f8d4d83-45a0-4cd1-b5fe-5cdcb7759a58');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a279e2db-d1ea-46cd-a70a-f5bd5e309bb1', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a279e2db-d1ea-46cd-a70a-f5bd5e309bb1' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a279e4a2-6084-42cc-a4fe-fbbdbee24661', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a279e4a2-6084-42cc-a4fe-fbbdbee24661' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27bc89d-003f-43d7-a224-d475719ed8b8', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27bc89d-003f-43d7-a224-d475719ed8b8' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27bc89d-062a-469c-9b70-355130a758e3', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27bc89d-062a-469c-9b70-355130a758e3' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27bc89d-09ff-4786-87a2-7ce447c9ca1c' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27bc89d-0fb1-4006-9467-99499994cb0f', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27bc89d-0fb1-4006-9467-99499994cb0f' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27d6bd6-f0f7-4fed-a905-68ecba250ce1' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a27d7977-69be-4948-bcc2-0c44235941b8', '9f495362-2497-452e-991c-2d2b83872242'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a27d7977-69be-4948-bcc2-0c44235941b8' AND role_id = '9f495362-2497-452e-991c-2d2b83872242');

INSERT INTO `permission_role_mapping` (permission_id, role_id)
SELECT 'a285abd3-fccb-4dfc-ac04-c431e8a1657d', '99bf20e7-3ad0-4423-836a-a1ea6929e704'
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM `permission_role_mapping` WHERE permission_id = 'a285abd3-fccb-4dfc-ac04-c431e8a1657d' AND role_id = '99bf20e7-3ad0-4423-836a-a1ea6929e704');

-- (2 row(s) skipped: orphaned role_id with no matching row in `roles`)

COMMIT;
