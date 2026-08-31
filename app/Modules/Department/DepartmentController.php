<?php 

declare(strict_types=1);

namespace App\Modules\Department;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Department\DepartmentService;
use App\Http\Api\V1\Department\DepartmentValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DepartmentController extends ClientController
{
    private static $module = 'departments.index';
    
    public function __construct(private DepartmentService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {
        guard(config('permissions.department-view'));
        return view('department.index')
            ->with('title', __('message.department_list'));
    }

    public function datalist(): mixed
    {
        guard(config('permissions.department-view'));
        try 
        {
            $result = $this->service->getDepartments();    
            return $this->success($result);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }   
    }

    public function create(): View
    {
        guard(config('permissions.department-create'));
        return view('department.form')
            ->with('title', __('message.add_department'))
            ->with('module_url', self::$module)
			->with('details', (object) $this->service->getDetails());
    }

    public function store(Request $request): mixed 
    {   
        guard(config('permissions.department-create'));
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
        guard(config('permissions.department-update'));
        return view('department.form')
            ->with('title', __('message.edit_department'))
            ->with('module_url', self::$module)
            ->with('id', $id)
            ->with('row', $this->service->findById($id))
            ->with('details', (object) $this->service->getDetails());
    }


    public function update(Request $request, string $id): mixed
    {
        guard(config('permissions.department-update'));
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