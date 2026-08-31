<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

class CustomUserPermissionMapper 
{
    public static function mapToUserPermissions(CustomUserPermissionDto $userPermissionDto) 
    {
        return array_map(fn ($permissionId) => ([
            'user_id' => $userPermissionDto->userId,
            'custom_permission_id' => $permissionId
        ]), $userPermissionDto->permissions);
    }
} 