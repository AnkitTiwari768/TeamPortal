<?php 

declare(strict_types=1);

namespace App\Shared\AccessControl;

trait UserAccessControl 
{
    protected function getAllUserPermissions(string $userId): array
    {
        $userPermissionsGranted = $this->getUserPermissionsGranted($userId);
        
        $userPermissionsGranted = ($userPermissionsGranted) ? $this->createPermissionIndex($userPermissionsGranted) : [];

        return array_unique($userPermissionsGranted);
    }

    protected function getUserPermissionsGranted(string $userId): array 
    {
        return \DB::table('users')
            ->select('permissions.slug')
            ->join('user_permissions', 'users.id', '=', 'user_permissions.user_id')
            ->join('permissions', 'user_permissions.permission_id', '=', 'permissions.id')
            ->where('user_permissions.user_id', $userId)
            ->get()
            ->toArray();
    }

    protected function getUserAuxiliaryPermissionsGranted(string $userId): array
    {
        return \DB::table('users')
            ->select('permissions.slug')
            ->join('temporary_user_permissions', 'users.id', '=', 'temporary_user_permissions.user_id')
            ->join('permissions', 'temporary_user_permissions.permission_id', '=', 'permissions.id')
            ->where('temporary_user_permissions.user_id', $userId)
            ->get()
            ->toArray();
    }

    protected function createPermissionIndex(array $permissions): array 
    {
        return array_column($permissions, 'slug');
    }
}