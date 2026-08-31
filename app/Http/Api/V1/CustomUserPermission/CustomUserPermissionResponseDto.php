<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

readonly class CustomUserPermissionResponseDto 
{
    public function __construct(
        public readonly array $permissions,
        public readonly array $selected_permissions
    )
    {
        
    }

    public static function fromModel(array $permissionData): self
    {
        [$permissions, $selectedPermissions] = $permissionData;
        
        return new self(
            permissions: $permissions,
            selected_permissions: $selectedPermissions
        );
    }
}