<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use Illuminate\Support\Facades\DB;

class SyncRoleTypePermissionsAction
{
    /**
     * Replaces a role type's permission set with exactly the ids supplied.
     *
     * Runs inside the caller's transaction (StoreRoleTypeAction) so a failure here rolls
     * the role_types row back as well. Deleting only what was removed and inserting only
     * what is new keeps the pivot free of duplicates and leaves untouched rows alone,
     * which matters because role_type_permissions has a composite primary key.
     */
    public function execute(string $roleTypeId, array $permissionIds): bool
    {
        $permissionIds = array_values(array_unique(array_filter($permissionIds)));

        $existing = $this->getAssignedPermissionIds($roleTypeId);

        $toRemove = array_diff($existing, $permissionIds);
        $toAdd = array_diff($permissionIds, $existing);

        if ($toRemove) {
            DB::table('role_type_permissions')
                ->where('role_type_id', $roleTypeId)
                ->whereIn('permission_id', $toRemove)
                ->delete();

            $roleIds = DB::table('roles')
                ->where('role_type_id', $roleTypeId)
                ->pluck('id')
                ->toArray();

            if (!empty($roleIds)) {
                DB::table('role_permissions')
                    ->whereIn('role_id', $roleIds)
                    ->whereIn('permission_id', $toRemove)
                    ->delete();

                $userIds = DB::table('user_roles')
                    ->whereIn('role_id', $roleIds)
                    ->pluck('user_id')
                    ->toArray();

                if (!empty($userIds)) {
                    DB::table('user_permissions')
                        ->whereIn('user_id', $userIds)
                        ->whereIn('permission_id', $toRemove)
                        ->delete();

                    if (auth()->check() && in_array(auth()->id(), $userIds)) {
                        $authService = app(\App\Http\Api\V1\Auth\AuthService::class);
                        $permissions = $authService->getUserPermissionsAssigned(auth()->id());
                        session(['permissions' => $permissions]);
                    }
                }
            }
        }

        if ($toAdd) {
            $toAdd = array_values($toAdd);

            $rows = array_map(
                fn(string $permissionId) => [
                    'role_type_id' => $roleTypeId,
                    'permission_id' => $permissionId
                ],
                $toAdd
            );

            foreach (array_chunk($rows, 1000) as $chunk) {
                DB::table('role_type_permissions')->insert($chunk);
            }

            $this->cascadeAdditionsToRoles($roleTypeId, $toAdd);
        }

        return true;
    }

    /**
     * Mirrors the removal cascade above for the opposite direction: a permission newly
     * granted to a Role Type must also become available to every role already using that
     * type (and every user holding one of those roles), otherwise Role List / Role Edit would
     * drift out of sync with Role Type Edit until each role was manually re-saved.
     *
     * Uses insertOrIgnore rather than delete-then-insert because this only ever ADDS grants:
     * a role_permissions row for (role, permission) can't already exist here (the permission
     * was, by definition, not yet on the role type), and a user_permissions row may already
     * exist with a different grant type (e.g. THROUGH_USER) that must not be clobbered.
     */
    private function cascadeAdditionsToRoles(string $roleTypeId, array $permissionIds): void
    {
        $roleIds = DB::table('roles')
            ->where('role_type_id', $roleTypeId)
            ->pluck('id')
            ->toArray();

        if (empty($roleIds)) {
            return;
        }

        $rolePermissionRows = [];
        foreach ($roleIds as $roleId) {
            foreach ($permissionIds as $permissionId) {
                $rolePermissionRows[] = ['role_id' => $roleId, 'permission_id' => $permissionId];
            }
        }

        foreach (array_chunk($rolePermissionRows, 1000) as $chunk) {
            DB::table('role_permissions')->insertOrIgnore($chunk);
        }

        $userIds = DB::table('user_roles')
            ->whereIn('role_id', $roleIds)
            ->pluck('user_id')
            ->unique()
            ->values()
            ->toArray();

        if (empty($userIds)) {
            return;
        }

        $userPermissionRows = [];
        foreach ($userIds as $userId) {
            foreach ($permissionIds as $permissionId) {
                $userPermissionRows[] = [
                    'user_id' => $userId,
                    'permission_id' => $permissionId,
                    'type' => \App\Contracts\GrantType::THROUGH_ROLE
                ];
            }
        }

        foreach (array_chunk($userPermissionRows, 1000) as $chunk) {
            DB::table('user_permissions')->insertOrIgnore($chunk);
        }

        if (auth()->check() && in_array(auth()->id(), $userIds)) {
            $authService = app(\App\Http\Api\V1\Auth\AuthService::class);
            $permissions = $authService->getUserPermissionsAssigned(auth()->id());
            session(['permissions' => $permissions]);
        }
    }

    public function getAssignedPermissionIds(string $roleTypeId): array
    {
        return DB::table('role_type_permissions')
            ->where('role_type_id', $roleTypeId)
            ->pluck('permission_id')
            ->toArray();
    }
}
