<?php

declare(strict_types=1);

namespace App\Web\Role;

use DB;
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeRepository;

/**
 * Builds the Add/Edit Role permission tree from the permissions granted to the selected
 * Role Type, instead of the permission_role_mapping subset the standalone Role Permission
 * screen uses.
 *
 * Overriding only the leaf lookup lets ModulePermissionTreeService's recursive module
 * walker be reused untouched -- the tree renders through the same markup and the same
 * tree.js as every other permission screen.
 */
class RoleTypeScopedPermissionTreeRepository extends ModulePermissionTreeRepository
{
    public function __construct(private string $roleTypeId) {}

    /**
     * Available permissions are the union of what the Role Type grants plus anything
     * directly assigned to this specific role in `role_permissions` -- a role can carry
     * direct permissions that predate its current Role Type (see role_role_type_permission_sync.sql),
     * and those must keep showing up here rather than only in the type-scoped list.
     */
    public function getPermissionsByModuleId(string $roleId, string $moduleId): array
    {
        return DB::table('permissions AS p')
            ->select('p.id', 'p.name')
            ->where('p.status', (int) config('constant.ACTIVE'))
            ->where('p.module_id', $moduleId)
            ->where(function ($query) use ($roleId) {
                $query->whereExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('role_type_permissions AS rtp')
                        ->whereColumn('rtp.permission_id', 'p.id')
                        ->where('rtp.role_type_id', $this->roleTypeId);
                })->orWhereExists(function ($sub) use ($roleId) {
                    $sub->selectRaw('1')
                        ->from('role_permissions AS rp')
                        ->whereColumn('rp.permission_id', 'p.id')
                        ->where('rp.role_id', $roleId);
                });
            })
            ->orderBy('p.name')
            ->get()
            ->toArray();
    }
}
