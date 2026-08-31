<?php declare(strict_types=1);

namespace App\Http\Api\V1\ModulePermissionTree;

use DB;
use App\Core\CoreRepository as Repository;

class ModulePermissionTreeRepository extends Repository
{
    public function getModulesByParentId(string | null $parentId): array 
    {
        return DB::table('modules')
            ->select('id', 'name', 'slug')
            ->where('parent_id', $parentId)
			->where('status', config('constant.ACTIVE'))
            ->get()
            ->toArray();
    }

    public function getPermissionsByModuleId(string $roleId, string $moduleId): array
    {
        return DB::table('permissions AS p')
            ->select('p.id', 'p.name')
            ->join('permission_role_mapping AS prm', 'p.id', '=', 'prm.permission_id')
            ->where('p.status', (int) config('constant.ACTIVE'))
            ->where('p.module_id', $moduleId)
            ->where('prm.role_id', $roleId)
            ->get()
            ->toArray();
    }

    /**
     * Available permissions to offer as checkboxes on the User Permission screen for the
     * given module: the union of the permission pool granted to the user's role(s) via
     * `role_permissions` and any permission already directly assigned to the user in
     * `user_permissions` -- a direct grant can exist outside the user's current role(s)
     * (e.g. after a role change) and must still be visible, or it gets silently dropped
     * the next time this screen is saved. Which of these are already checked for the user
     * is decided separately by UserPermissionRepository::getPermissionsByUserId().
     */
    public function getPermissionsByUserId(string $userId, ?string $moduleId = null) : array
    {
        return DB::table('permissions AS p')
            ->select('p.id', 'p.name')
            ->where('p.status', (int) config('constant.ACTIVE'))
            ->where('p.module_id', $moduleId)
            ->where(function ($query) use ($userId) {
                $query->whereExists(function ($sub) use ($userId) {
                    $sub->selectRaw('1')
                        ->from('role_permissions AS rp')
                        ->whereColumn('rp.permission_id', 'p.id')
                        ->whereIn('rp.role_id', function ($roleQuery) use ($userId) {
                            $roleQuery->select('role_id')
                                ->from('user_roles')
                                ->where('user_id', $userId);
                        });
                })->orWhereExists(function ($sub) use ($userId) {
                    $sub->selectRaw('1')
                        ->from('user_permissions AS up')
                        ->whereColumn('up.permission_id', 'p.id')
                        ->where('up.user_id', $userId);
                });
            })
            ->orderBy('p.name')
            ->get()
            ->toArray();
    }

    
}