<?php 

declare(strict_types=1);

namespace App\Modules\Designation;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Designation\DesignationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Http\Api\V1\Designation\DesignationValidation as Validation;

final class DesignationController extends ClientController
{
    private static $module = 'designations.index';
    
    public function __construct(private DesignationService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.designation-view'));
        return view('designation.index')
            ->with('title', __('message.designation_list'));
    }

    public function datalist(): mixed
    {
        guard(config('permissions.designation-view'));
        try 
        {
            $result = $this->service->getDesignations();    
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function create(): View
    {
        guard(config('permissions.designation-create'));
        return view('designation.form')
            ->with('title', __('message.add_designation'))
            ->with('module_url', self::$module)
			->with('details', (object) $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
        guard(config('permissions.designation-create'));
        try 
        {
			$validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
            $result = $this->service->create($validator->validated());
            return $this->created($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function edit(string $id): View
    {
        guard(config('permissions.designation-update'));
        return view('designation.form')
            ->with('title', __('message.edit_designation'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
			->with('details', (object) $this->service->getDetails());
    }

    public function update(Request $request, string $id): mixed
    {
        guard(config('permissions.designation-update'));
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
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
        
    }
}