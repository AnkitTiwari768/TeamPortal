<?php 

declare (strict_types = 1);   

namespace App\Http\Api\V1\RolePermission;

class RolePermissionRequest
{
    public static function getRules(?string $id = null): array
    {
        return [
            'role_id' => 'required|string|string|exists:roles,id',
            // Nullable/empty on purpose: unchecking every permission in the tree is a valid
            // "clear all permissions for this role" submission, not an invalid one.
            'permissions' => 'nullable|array',
            'permissions.*' => 'required|string|exists:permissions,id',
        ];
    }
}