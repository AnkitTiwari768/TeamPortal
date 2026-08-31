<?php 

declare(strict_types=1);

namespace App\Web\Permission;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Permission\{PermissionService, PermissionRequest};

class PermissionController extends ClientController
{
    private static string $module = 'permissions.index';

    public function __construct(private PermissionService $permissionService){}

    public function index(): View
    {
        guard(config('permissions.permission-view'));
        
        $title = __('message.permission_list');
        
        return view('permissions.index', compact('title'));  
    }

    public function create(): View
    {
        guard(config('permissions.permission-create'));
        
        $title = __('message.add_permission');
        $module_url = static::$module;

        $modules = $this->permissionService->getModuleTree();
        return view('permissions.form', compact('title', 'module_url', 'modules'));
    }

    public function edit(string $id): View
    {
        guard(config('permissions.permission-update'));
        
        $title = __('message.edit_permission');
        $module_url = static::$module;
        
        $row = $this->permissionService->getPermission($id);
        $modules = $this->permissionService->getModuleTree();
        $permissionRoles = $this->permissionService->getPermissionRoles($id);
        // dd($row,$modules,$permissionRoles);
        return view('permissions.form', compact('title', 'module_url', 'modules', 'row', 'id', 'permissionRoles'));
            
    }

    public function getPermissions()
    {
        guard(config('permissions.permission-view'));
        
        return $this->success($this->permissionService->getPermissions());
    }
    
    public function createPermission(Request $request) 
    {   
        guard(config('permissions.permission-create'));
        
        $validator = Validator::make($request->all(), PermissionRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->permissionService->storePermission($validator->validated())
        );
    }

    public function updatePermission(Request $request, string $id) 
    {   
        guard(config('permissions.permission-update'));
        
        $validator = Validator::make($request->all(), PermissionRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->permissionService->storePermission($validator->validated(), $id)
        );
    }
}