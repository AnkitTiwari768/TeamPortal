<?php 

declare(strict_types=1);

namespace App\Modules\Role;

use App\Http\Controllers\ClientController;
use App\Api\V1\Role\RoleService;
use App\Http\Api\V1\Role\RoleValidation as Validation;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class RoleController extends ClientController
{
    private static $module = 'roles.index';
    
    public function __construct(private RoleService $roleService){}

    public function index(): View
    {
        guard(config('permissions.role-view'));
        return view('roles.index')
            ->with('title', __('message.role_list'))
			->with('details', null);
    }


    public function datalist(): mixed
    {
		guard(config('permissions.role-view'));
        
        try 
        {
            $result = $this->roleService->getRoles();    
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }


    public function create(): View
    {
        guard(config('permissions.role-create'));
        return view('roles.form')
            ->with('title', __('message.add_role'))
            ->with('module_url', self::$module)
            ->with('details', (object) $this->service->getDetails());
    }


    public function store(Request $request): mixed 
    {   
        guard(config('permissions.role-create'));
        try 
        { 
			$validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
               
            $response = $this->service->save($validator->validated());
            return $this->created($response);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }



    public function edit(string $id): View
    {
		guard(config('permissions.role-update'));
        return view('roles.form')
            ->with('title', __('message.edit_role'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
            ->with('details', (object) $this->service->getDetails($id));
    }


    public function update(Request $request, string $id): mixed
    {
		guard(config('permissions.role-update'));
         try 
        {
			$validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
               
            return $this->service->save($validator->validated(), $id);
            //return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handleException($e);
        }
        
    }
	
	
	public function getRole(string $categoryId): mixed
    {
        return $this->service->getRole($categoryId);    
    }
}