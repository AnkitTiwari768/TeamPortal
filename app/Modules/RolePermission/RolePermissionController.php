<?php declare(strict_types=1); 
namespace App\Modules\RolePermission; 
use App\Http\Controllers\ClientController;
use App\Exceptions\ValidationException;
use App\Http\Api\V1\RolePermission\RolePermissionService as Service;
use App\Http\Api\V1\RolePermission\RolePermissionValidation as Validation; 
use Illuminate\Http\Request;
use App\Http\Api\V1\ModulePermissionTree\{ModulePermissionTreeService, ModulePermissionTreeRepository};

final class RolePermissionController extends ClientController 
{
	protected $module_url='roles.index';
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }


    public function edit($id)
    {
       guard(config('permissions.permission-button-view'));
		$module_url=$this->module_url;
        $details = $this->service->getDetails($id);
        ['roleName' => $role_name,'rolePermissions' => $role_permissions] = $details;
        // dd($this->service->getDetails($id));
		$permissions = (new ModulePermissionTreeService(new ModulePermissionTreeRepository))->getModulePermissionTree($id);
		//dd($permissions);
        return view('roles.role_permission', compact('id','module_url','role_name','permissions','role_permissions'))
            ->with('title', __('message.role_permission'));
    }

    public function show($id)
    {
       guard(config('permissions.permission-button-view'));
        try 
        {  
            $response['permissions'] = $this->service->findById($id); 
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
    
    public function store(Request $request) 
    { 
	    guard(config('permissions.permission-button-view'));
        try 
        {
           $validator=Validation::getRules($request->all(),null);
            if ($validator->fails()) 
               return $this->error($validator->errors()); 
            $response = $this->service->save($validator->validated());
            return $this->created($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    } 
}