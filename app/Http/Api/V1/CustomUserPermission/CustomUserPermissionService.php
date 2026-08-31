<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

class CustomUserPermissionService
{
    public function __construct(private CustomUserPermissionRepository $userPermissionRepository)
    {
        
    }

    public function getUserPermissions(string $userId)
    {
        return $this->userPermissionRepository->getCustomPermissions($userId);
    }

    public function storeUserPermissions(CustomUserPermissionDto $userPermissionDto)
    {
        return $this->userPermissionRepository->storeUserPermissions($userPermissionDto);
    }
}