<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use DB;

/**
 * Registers the User Activity Logs module in the navigation tree and creates its (read-only)
 * permission.
 *
 * Idempotent: every row is looked up by its natural key first, so the seeder can be re-run
 * on an environment that already has part of the module without creating duplicates.
 *
 *   php artisan db:seed --class=Database\\Seeders\\UserLogPermissionSeeder
 */
class UserLogPermissionSeeder extends Seeder
{
    private const MODULE_SLUG = 'user-logs';
    private const PARENT_MODULE_SLUG = 'user-management';
    private const ADMIN_ROLE_SLUG = 'administrator';

    /**
     * Permission slugs must match the keys added to config/permissions.php, since acl()
     * compares the session's permission slugs against those config values.
     */
    private const PERMISSIONS = [
        'User Log View' => 'user-log-view',
    ];

    public function run(): void
    {
        $moduleId = $this->ensureModule();

        if (! $moduleId) {
            $this->command?->error('User Logs module could not be created: no user row is available for modules.created_by.');
            return;
        }

        $permissionIds = $this->ensurePermissions($moduleId);

        $this->grantToAdministrator($permissionIds);
        $this->grantToAdministratorRoleType($permissionIds);

        $this->command?->info('User Logs module permissions seeded (' . count($permissionIds) . ' permissions).');
    }

    private function ensureModule(): ?string
    {
        $module = DB::table('modules')->select('id')->where('slug', self::MODULE_SLUG)->first();

        if ($module) {
            return $module->id;
        }

        $parentId = DB::table('modules')
            ->select('id')
            ->where('slug', self::PARENT_MODULE_SLUG)
            ->first()
            ?->id;

        // modules.created_by is NOT NULL, so fall back to any existing user when the
        // administrator cannot be resolved.
        $createdBy = DB::table('users AS u')
            ->select('u.id')
            ->join('user_roles AS ur', 'ur.user_id', '=', 'u.id')
            ->join('roles AS r', 'r.id', '=', 'ur.role_id')
            ->where('r.slug', self::ADMIN_ROLE_SLUG)
            ->value('u.id')
            ?? DB::table('users')->value('id');

        if (! $createdBy) {
            return null;
        }

        $sortOrder = (int) DB::table('modules')
            ->where('parent_id', $parentId)
            ->max('sort_order');

        $moduleId = (string) Str::orderedUuid();

        DB::table('modules')->insert([
            'id' => $moduleId,
            'parent_id' => $parentId,
            'name' => 'User Activity Logs',
            'slug' => self::MODULE_SLUG,
            'url' => 'user-logs',
            'sort_order' => $sortOrder + 1,
            'type' => 1,
            'status' => config('constant.ACTIVE'),
            'created_at' => currentDateTime(),
            'created_by' => $createdBy,
        ]);

        return $moduleId;
    }

    /**
     * @return string[] permission ids
     */
    private function ensurePermissions(string $moduleId): array
    {
        $permissionIds = [];

        foreach (self::PERMISSIONS as $name => $slug) {
            $existing = DB::table('permissions')->select('id')->where('slug', $slug)->first();

            if ($existing) {
                $permissionIds[] = $existing->id;
                continue;
            }

            $permissionId = (string) Str::orderedUuid();

            DB::table('permissions')->insert([
                'id' => $permissionId,
                'name' => $name,
                'slug' => $slug,
                'description' => $name . ' permission for the User Activity Logs module.',
                'module_id' => $moduleId,
                'status' => config('constant.ACTIVE'),
                'created_at' => currentDateTime(),
                'updated_at' => currentDateTime(),
            ]);

            $permissionIds[] = $permissionId;
        }

        return $permissionIds;
    }

    /**
     * Mirrors how the existing Role Type module permissions are wired: assignable to the
     * Administrator role (permission_role_mapping) and already granted to it
     * (role_permissions), so the module is reachable straight after seeding.
     */
    private function grantToAdministrator(array $permissionIds): void
    {
        if (! $permissionIds) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('slug', self::ADMIN_ROLE_SLUG)->value('id');

        if (! $adminRoleId) {
            $this->command?->warn('Administrator role not found; User Log permissions were created but not granted.');
            return;
        }

        foreach ($permissionIds as $permissionId) {
            $mappingExists = DB::table('permission_role_mapping')
                ->where('role_id', $adminRoleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $mappingExists) {
                DB::table('permission_role_mapping')->insert([
                    'role_id' => $adminRoleId,
                    'permission_id' => $permissionId,
                ]);
            }

            $grantExists = DB::table('role_permissions')
                ->where('role_id', $adminRoleId)
                ->where('permission_id', $permissionId)
                ->exists();

            if (! $grantExists) {
                DB::table('role_permissions')->insert([
                    'role_id' => $adminRoleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }

    /**
     * The "Role List -> Assign Permission" screen (RoleTypeScopedPermissionTreeRepository)
     * offers only permissions already present in role_type_permissions for the role's Role
     * Type -- granting the Administrator ROLE above (permission_role_mapping/role_permissions)
     * does not put it there, so without this the new permission (and, if it were the only
     * grantable item left under its parent, the whole "User Management" node) never appears
     * on that screen. Uses the existing SyncRoleTypePermissionsAction merged with the Role
     * Type's current permissions -- calling it with only the new id would replace the whole
     * set and strip everything else the Role Type already grants.
     */
    private function grantToAdministratorRoleType(array $permissionIds): void
    {
        if (! $permissionIds) {
            return;
        }

        $adminRoleId = DB::table('roles')->where('slug', self::ADMIN_ROLE_SLUG)->value('id');
        $roleTypeId = $adminRoleId ? DB::table('roles')->where('id', $adminRoleId)->value('role_type_id') : null;

        if (! $roleTypeId) {
            $this->command?->warn('Administrator Role Type not found; User Log permission was not added to the Assign Permission screen.');
            return;
        }

        $action = new \App\Web\RoleType\SyncRoleTypePermissionsAction();
        $currentPermissionIds = $action->getAssignedPermissionIds($roleTypeId);

        $action->execute($roleTypeId, [...$currentPermissionIds, ...$permissionIds]);
    }
}
