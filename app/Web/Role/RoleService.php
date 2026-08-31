<?php

declare(strict_types=1);

namespace App\Web\Role;

use Illuminate\Support\Facades\DB;
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeService;
use App\Http\Api\V1\RolePermission\RolePermissionService;

class RoleService
{
    public function getRoles(): array
    {
        return DB::table('roles')
            ->where('status', true)
            ->pluck('name', 'id')
            ->toArray();
    }

    public function roleTypeExists(string $roleTypeId): bool
    {
        return DB::table('role_types')->where('id', $roleTypeId)->exists();
    }

    /**
     * Permission tree for the Add/Edit Role form: the nodes come from the selected Role
     * Type, the checked state from the role's own role_permissions rows.
     *
     * Because the tree is rebuilt from the server on every Role Type change, switching type
     * cannot leave a stale selection behind -- anything not offered by the new type simply
     * is not rendered.
     */
    public function getRoleTypePermissionTree(string $roleTypeId, ?string $roleId = null): array
    {
        $permissions = (new ModulePermissionTreeService(new RoleTypeScopedPermissionTreeRepository($roleTypeId)))
            ->getModulePermissionTree($roleId ?? '');

        return [
            'permissions' => $permissions,
            'rolePermissions' => $roleId
                ? app(RolePermissionService::class)->getRolePermissions($roleId)
                : []
        ];
    }
}
