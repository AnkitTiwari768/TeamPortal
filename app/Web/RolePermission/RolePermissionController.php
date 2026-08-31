<?php 

declare(strict_types=1);

namespace App\Web\RolePermission;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\RolePermission\{RolePermissionService, RolePermissionRequest};
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeService;
use App\Web\Role\RoleTypeScopedPermissionTreeRepository;

class RolePermissionController extends ClientController
{
    private static string $module = 'roles.index';

    public function __construct(private RolePermissionService $rolePermissionService){}

    /**
     * The tree offered here is scoped to the role's Role Type -- the same permission pool the
     * Add/Edit Role form offers -- so this screen can never check a permission Role Type Edit
     * has not granted. A role with no Role Type assigned has no permission pool to offer.
     */
    public function edit(string $id): View
    {
        guard(config('permissions.permission-button-view'));

        $title = __('message.role_permission');
        $module_url = static::$module;

        $roleName = $this->rolePermissionService->getRoleName($id);
        $rolePermissions = $this->rolePermissionService->getRolePermissions($id);
        $roleTypeId = $this->rolePermissionService->getRoleTypeId($id);

        $permissions = $roleTypeId
            ? (new ModulePermissionTreeService(new RoleTypeScopedPermissionTreeRepository($roleTypeId)))->getModulePermissionTree($id)
            : [];

        return view('roles.role_permission', compact('id', 'title', 'module_url', 'roleName', 'permissions', 'rolePermissions', 'roleTypeId'));
    }

    public function storeRolePermission(Request $request) 
    { 
	    guard(config('permissions.permission-button-view'));
       
        $validator = Validator::make($request->all(), RolePermissionRequest::getRules());

        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
        
        return $this->created(
            $this->rolePermissionService->storeRolePermission($validator->validated())
        );
    } 
}