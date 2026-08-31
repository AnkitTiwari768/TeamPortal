<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use DB;

/**
 * Registers the Role Type module in the navigation tree and creates its action permissions.
 *
 * Idempotent: every row is looked up by its natural key first, so the seeder can be re-run
 * on an environment that already has part of the module without creating duplicates.
 *
 *   php artisan db:seed --class=Database\\Seeders\\RoleTypePermissionSeeder
 */
class RoleTypePermissionSeeder extends Seeder
{
    private const MODULE_SLUG = 'role-types';
    private const PARENT_MODULE_SLUG = 'user-management';
    private const ADMIN_ROLE_SLUG = 'administrator';

    /**
     * Permission slugs must match the keys added to config/permissions.php, since acl()
     * compares the session's permission slugs against those config values.
     */
    private const PERMISSIONS = [
        'Role Type View' => 'role-type-view',
        'Role Type Create' => 'role-type-create',
        'Role Type Update' => 'role-type-update',
        'Role Type Permission View' => 'role-type-permission-view',
    ];

    public function run(): void
    {
        $moduleId = $this->ensureModule();

        if (! $moduleId) {
            $this->command?->error('Role Type module could not be created: no user row is available for modules.created_by.');
            return;
        }

        $permissionIds = $this->ensurePermissions($moduleId);

        $this->grantToAdministrator($permissionIds);

        $this->command?->info('Role Type module permissions seeded (' . count($permissionIds) . ' permissions).');
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
            'name' => 'Role Types',
            'slug' => self::MODULE_SLUG,
            'url' => 'role-types',
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
                'description' => $name . ' permission for the Role Type module.',
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
     * Mirrors how the existing Role module permissions are wired: assignable to the
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
            $this->command?->warn('Administrator role not found; Role Type permissions were created but not granted.');
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
}
