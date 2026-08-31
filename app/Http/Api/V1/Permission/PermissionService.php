<?php 

declare (strict_types = 1);

namespace App\Http\Api\V1\Permission;

use App\Traits\NestableModule;
use App\Http\Services\ApiService;
use App\Http\Api\V1\Permission\Permission; 

class PermissionService extends ApiService 
{
    use NestableModule;
    
    protected array $columns = [
	    1 => 'module_name',
        2 => 'name',
        3 => 'slug',
    ];

    public function getPermissions()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $search ??= $this->escape_special_characters($search);

		$query = Permission::select(
            'permissions.id', 
            'permissions.name', 
            'permissions.status', 
            'm.name AS module_name'
        );

		$query->join('modules AS m', 'm.id', '=', 'permissions.module_id');

        if ($search)
        {
            $query->where(function($query) use ($search) {
                $query
                    ->where('m.name','LIKE',"%$search%")
                    ->orWhere('permissions.name','LIKE',"%$search%")
                    ->orWhere('permissions.slug','LIKE',"%$search%");
            });
        }

        if (isset($filters['module_id']) && !empty($filters['module_id']))
        {
            $query->where('m.id', $filters['module_id']);
        }

        $query->orderBy($order, $dir);

        if ($page) 
        {
            return $this->getDataTableResult(
                PermissionResource::collection($query->paginate($limit))
            );
        }

        return PermissionResource::collection($query->get());
    }

    public function storePermission(array $payload, ?string $permissionId = null): bool
    { 
		return \DB::transaction(function () use ($payload, $permissionId) {
            
			$permissionData = [
                'name' => ucwords($payload['name']),
                'slug' => slugify($payload['name']),
				'module_id' => $payload['module_id'],
                'description' => $payload['description'],
                'created_at' => currentDateTime(),
            ];

            if (! $permissionId) 
            {
                $permissionData['created_at'] = currentDateTime();
                $permissionData['created_by'] = AuthId();
                $permissionData['updated_at'] = currentDateTime();
                $permissionData['updated_by'] = AuthId();

                $permission = Permission::create($permissionData);
                $permissionId = $permission->id;
            }
            else 
            {
                $permissionData['updated_at'] = currentDateTime();
                $permissionData['updated_by'] = AuthId();
                
                $permission = Permission::findOrFail($permissionId);
                $permission->fill($permissionData)->save();
            }

            \DB::table('permission_role_mapping')
                ->where('permission_id', $permissionId)
                ->delete();

            $permissionRoleData = [];

            foreach ($payload['roles'] as $roleId) 
            {
                $permissionRoleData[] = [
                    'permission_id' => $permissionId,
                    'role_id' => $roleId
                ];
            }

            if ($permissionRoleData) 
            {
                \DB::table('permission_role_mapping')
                    ->insert($permissionRoleData);
            }
            
            return true;
        });
    }
	
    public function getPermission(string $permissionId)
    {
        return Permission::findOrFail($permissionId);
    }

    public function getPermissionRoles(string $permissionId)
    {
        return \DB::table('permission_role_mapping AS prm')
                    ->select('r.id', 'r.name AS text')
                    ->join('roles AS r', 'prm.role_id', '=', 'r.id')
                    ->where('prm.permission_id', $permissionId)
                    ->get()
                    ->toArray();
    }

	public function getModuleTree()
    {
        return $this->buildModuleTree();
    }		
}