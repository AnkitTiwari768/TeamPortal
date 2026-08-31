<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeService;

class GetRoleTypePermissionTreeAction
{
    /**
     * Returns the full module -> permission tree together with the ids already granted to
     * the given role type, so the same partial can render both the Add (nothing checked)
     * and Edit (existing permissions checked) states.
     */
    public function execute(?string $roleTypeId = null): array
    {
        $permissions = (new ModulePermissionTreeService(new RoleTypePermissionTreeRepository))
            ->getModulePermissionTree($roleTypeId ?? '');

        return [
            'permissions' => $permissions,
            'assignedPermissions' => $roleTypeId
                ? (new SyncRoleTypePermissionsAction)->getAssignedPermissionIds($roleTypeId)
                : []
        ];
    }
}
