<?php declare(strict_types=1);
namespace App\Modules\Permission;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Permission\PermissionService as Service;  
use App\Http\Api\V1\Permission\PermissionValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class PermissionController extends ClientController 
{
	private static $module='permissions.index';
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.permission-view'));
        return view('permissions.index')
            ->with('title', __('message.permission_list'));
    }

    public function datalist()
    {
        guard(config('permissions.permission-view'));
        $response = $this->service->getPermissions(); 
        return $this->success($response);
    }

    public function create()
    { 
		guard(config('permissions.permission-create'));
        return view('permissions.form')
            ->with('title', __('message.add_permission'))        
            ->with('module_url', self::$module)   
			->with('modules',$this->service->getNestedModules())
            ->with('details', $this->service->getDetails());
    }

    public function store(Request $request) 
    {
       guard(config('permissions.permission-update'));
        try 
        {
			$validator=Validation::getRules($request->all(),null);
            if ($validator->fails()) 
               return $this->error($validator->errors()); 
            $response = $this->service->create($validator->validated());
            return $this->created($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }

    public function edit($id)
    {
        guard(config('permissions.permission-update'));

        return view('permissions.form')
            ->with('title', __('message.edit_permission'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
			->with('modules',$this->service->getNestedModules())
            ->with('details', $this->service->getDetails($id));
    }

    public function show($id)
    {
       guard(config('permissions.permission-update'));
        try 
        {
            $response = $this->service->findById($id);
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }


    public function update(Request $request, $id) 
    {
        guard(config('permissions.permission-update'));
        try 
        {
			$validator=Validation::getRules($request->all(),(string) $id);
            if ($validator->fails()) 
               return $this->error($validator->errors());

            $this->service->create($validator->validated(), $id);
            return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }
	
	/*public function destroy($id)
    {
        guard(config('permissions.permission-delete'));
        try 
        {
            $this->service->destroy($id);
            return $this->deleted();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }*/
}