<?php 
declare(strict_types=1);
namespace App\Modules\Module;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Module\ModuleService;
use App\Http\Api\V1\Module\ModuleValidation as Validation;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class ModuleController extends ClientController
{
    private static $module = 'modules.index';
    
    public function __construct(private ModuleService $service)
    {
        $this->service = $service;
    }


    public function index(): View
    {
        guard(config('permissions.module-view'));
        return view('modules.index')
            ->with('title', __('message.module_list'));
    }


    public function datalist(): mixed
    {
		guard(config('permissions.module-view'));
        try 
        {
            $result = $this->service->getModules();    
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }


    public function create(): View
    {
		guard(config('permissions.module-create'));
        return view('modules.form')
            ->with('title', __('message.add_module'))
            ->with('module_url', self::$module)
			->with('modules',$this->service->getNestedModules())
            ->with('details', (object) $this->service->getDetails());
    }


    public function store(Request $request): mixed 
    {   
	    guard(config('permissions.module-create'));
        try 
        { 
			$validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
               
           //$response = $this->service->create($validator->validated());
		   $response = $this->service->create($request->all());
           return $this->created($response);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }



    public function edit(string $id): View
    {
		 guard(config('permissions.module-update'));
        //  dd($this->service->findById($id),$this->service->getNestedModules(),$this->service->getDetails());
        return view('modules.form')
            ->with('title', __('message.edit_module'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
			->with('modules',$this->service->getNestedModules())
            ->with('details', (object) $this->service->getDetails());
    }


    public function update(Request $request, string $id): mixed
    {
		guard(config('permissions.module-update'));
         try 
        {
			$validator = Validation::getRules($request->all(), $id);
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
               
            return $this->service->update($validator->validated(), $id);
            //return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handleException($e);
        }
        
    }
	
}