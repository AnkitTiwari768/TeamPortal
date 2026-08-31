<?php 

declare (strict_types = 1);

namespace App\Http\Api\V1\UserPermission;

use App\Contracts\GrantType;


class UserPermissionRepository
{
    public function save(UserPermissionDTO $userPermissionDTO) : bool
    {
        return \DB::transaction(function() use ($userPermissionDTO) {

            $existingUserPermissions = \DB::table('user_permissions')
                ->select('permission_id')
                ->where('user_id', $userPermissionDTO->userId)
                ->get()
                ->toArray();

            $userPermissionsThroughRole = \DB::table('user_permissions')
                ->select('permission_id')
                ->where('user_id', $userPermissionDTO->userId)
                ->where('type', GrantType::THROUGH_ROLE)
                ->get()
                ->toArray();
            
            $userPermissionsThroughUser = $userPermissionDTO->userPermissions;
            $existingUserPermissions = $existingUserPermissions ? array_column($existingUserPermissions, 'permission_id') : [];
            $userPermissionsThroughRole = array_column($userPermissionsThroughRole, 'permission_id');
            $revokedPermissions = array_diff($existingUserPermissions, $userPermissionsThroughUser);
            $newUserPermissionsThroughRole = array_intersect($userPermissionsThroughRole, $userPermissionsThroughUser);
            $newUserPermissionsThroughUser = array_diff($userPermissionsThroughUser, $newUserPermissionsThroughRole);

            $newUserPermissionsThroughRole = array_map(
                fn ($permissionId) => ([
                    'user_id' => $userPermissionDTO->userId,
                    'permission_id' => $permissionId,
                    'type' => GrantType::THROUGH_ROLE
                ]),
                $newUserPermissionsThroughRole
            );

            $newUserPermissionsThroughUser = array_map(
                fn ($permissionId) => ([
                    'user_id' => $userPermissionDTO->userId,
                    'permission_id' => $permissionId,
                    'type' => GrantType::THROUGH_USER
                ]),
                $newUserPermissionsThroughUser
            ); 

            $updatedRevokedPermissions = array_map(
                fn ($permissionId) => ([
                    'user_id' => $userPermissionDTO->userId,
                    'permission_id' => $permissionId
                ]),
                $revokedPermissions
            );

            $updatedUserPermissions = array_merge($newUserPermissionsThroughRole, $newUserPermissionsThroughUser);

            \DB::table('user_permissions')
                ->where('user_id', $userPermissionDTO->userId)
                ->delete();

            \DB::table('user_permissions')
                ->insert($updatedUserPermissions);
            
            \DB::table('permissions_revoked')
                ->insert($updatedRevokedPermissions);

            \DB::table('permissions_revoked')
                ->where('user_id', $userPermissionDTO->userId)
                ->whereIn('permission_id', $userPermissionsThroughUser)
                ->delete();

            return true;
        });
    }

    public function getUserNameById(string $userId): string | null
    {
        $result = \DB::table('users')
            ->selectRaw('concat_ws(" ", first_name, middle_name, last_name) as name')
            ->where('id', $userId)
            ->first();
        
        return $result->name ?? null;
    }

    public function getPermissionsByUserId(string $userId): array
    {
        return \DB::table('user_permissions')
            ->select('permission_id')
            ->where('user_id', $userId)
            ->get()
            ->toArray();
    }

    /**
     * Permissions currently granted to the user (via role or direct assignment), grouped-ready
     * for the User View screen. Mirrors RoleService::getAssignedPermissions() so both "View"
     * pages render the same shape/markup.
     */
    public function getAssignedPermissions(string $userId): array
    {
        return \DB::table('user_permissions as up')
            ->select('p.id', 'p.name', 'p.slug', 'm.name as module_name')
            ->join('permissions as p', 'p.id', '=', 'up.permission_id')
            ->leftJoin('modules as m', 'm.id', '=', 'p.module_id')
            ->where('up.user_id', $userId)
            ->orderBy('m.name')
            ->orderBy('p.name')
            ->get()
            ->map(fn ($row) => (array) $row)
            ->toArray();
    }
}