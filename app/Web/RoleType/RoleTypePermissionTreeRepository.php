<?php

declare(strict_types=1);

namespace App\Web\RoleType;

use DB;
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeRepository;

/**
 * Role Types are a global master, so their form must offer EVERY active permission rather
 * than the role-scoped subset that permission_role_mapping restricts the Role Permission
 * screen to. Overriding just the leaf lookup lets us reuse ModulePermissionTreeService's
 * recursive module tree builder untouched.
 */
class RoleTypePermissionTreeRepository extends ModulePermissionTreeRepository
{
    public function getPermissionsByModuleId(string $roleId, string $moduleId): array
    {
        return DB::table('permissions AS p')
            ->select('p.id', 'p.name')
            ->where('p.status', (int) config('constant.ACTIVE'))
            ->where('p.module_id', $moduleId)
            ->orderBy('p.name')
            ->get()
            ->toArray();
    }
}
