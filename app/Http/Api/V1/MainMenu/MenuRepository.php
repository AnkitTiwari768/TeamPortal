<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\MainMenu;

use Illuminate\Support\Facades\DB;

class MenuRepository 
{
    public function getUserNavigationMenus(string $userId, ?string $activeRole = null)
    {
        if ($activeRole === null) {
            $activeRole = session('active_role');
        }

        $menusByUserPermissionsQuery = $this->prepareMenusByUserPermissionsQuery($userId);
        $menusByRolePermissionsQuery = $this->prepareMenusByRolePermissionsQuery($userId, $activeRole);
        $menusByUserCustomPermissionQuery = $this->prepareMenusByUserCustomPermissionQuery($userId);
        
        return $menusByUserPermissionsQuery
            ->union($menusByRolePermissionsQuery)
            ->union($menusByUserCustomPermissionQuery)
            ->pluck('url')
            ->toArray();
    }

    private function prepareMenusByUserPermissionsQuery(string $userId)
    {
        $query = DB::table('user_permissions AS up')
            ->select('m.url')
            ->join('permissions AS p', 'up.permission_id', '=', 'p.id')
            ->join('modules AS m', 'p.module_id', '=', 'm.id')
            ->where('up.user_id', $userId);
        
       

        return $query;
    }

    private function prepareMenusByRolePermissionsQuery(string $userId, ?string $activeRole = null)
    {
        $query = DB::table('role_permissions as rp')
            ->select('m.url')
            ->join('permissions as p', 'rp.permission_id', '=', 'p.id')
            ->join('modules AS m', 'p.module_id', '=', 'm.id')
            ->whereIn('rp.role_id', function ($query) use ($userId, $activeRole) {
                $q = $query->select('ur.role_id')
                    ->from('user_roles as ur')
                    ->where('ur.user_id', $userId);

                if ($activeRole !== null) {
                    $q->join('roles as r', 'ur.role_id', '=', 'r.id')
                      ->where('r.slug', $activeRole);
                }
            });

       

        return $query;
    }

    private function prepareMenusByUserCustomPermissionQuery(string $userId)
    {
        $query = DB::table('custom_permission_groups as cpg')
            ->select('m.url')
            ->join('permissions as p', 'cpg.permission_id', '=', 'p.id')
            ->join('modules AS m', 'p.module_id', '=', 'm.id')
            ->whereIn('cpg.custom_permission_id', function ($query) use ($userId) {
                $query->select('ucp.custom_permission_id')
                    ->from('user_custom_permissions as ucp')
                    ->where('ucp.user_id', $userId);
            });

       

        return $query;
    }

    
}