<?php

declare(strict_types=1);

namespace App\Http\Api\V1\UserPermission;

use App\Http\Services\ApiService;

class UserPermissionService extends ApiService
{
    protected UserPermissionRepository $userPermissionRepository;
    protected CreateOrUpdateUserPermissionAction $action;

    public function __construct(
        UserPermissionRepository $userPermissionRepository,
        CreateOrUpdateUserPermissionAction $action
    ) {
        $this->userPermissionRepository = $userPermissionRepository;
        $this->action = $action;
    }

    public function getDetails(string $userId): array
    {
        return [
            'userName' => $this->userPermissionRepository->getUserNameById($userId),
            'userPermissions' => $this->getPermissionsByUserId($userId)
        ];
    }

    public function save(UserPermissionDTO $userPermissionDTO): bool
    {
        return $this->action->execute($userPermissionDTO);
    }

    public function getAssignedPermissions(string $userId): array
    {
        return $this->userPermissionRepository->getAssignedPermissions($userId);
    }

    private function getPermissionsByUserId(string $userId): array
    {
        return ($permissions = $this->userPermissionRepository->getPermissionsByUserId($userId))
            ? array_column($permissions, 'permission_id')
            : [];
    }
}
