<?php 
declare(strict_types=1);
namespace App\Web\Department;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController; 
use App\Http\Api\V1\Department\DepartmentValidation as Validation;
use App\Http\Api\V1\Department\{DepartmentService, DepartmentRequest};


class DepartmentController extends ClientController
{
    private static string $module = 'departments.index';

    public function __construct(private DepartmentService $service){}

    public function index(): View
    {
        guard(config('permissions.department-view'));        
        $title = __('message.department_list');        
        return view('department.index1', compact('title'));  
    }

    public function create(): View
    {
        guard(config('permissions.department-create'));        
        $title = __('message.add_department');
        $module_url = static::$module;
        return view('department.form1', compact('title', 'module_url'));
    }

    public function edit(string $id): View
    {
        guard(config('permissions.department-update'));        
        $title = __('message.edit_department');
        $module_url = static::$module;        
        $row = $this->service->getDepartment($id);
        return view('department.form1', compact('title', 'module_url', 'row', 'id'));
            
    }

    public function getDepartments()
    {
        guard(config('permissions.department-view'));
        
        return $this->success($this->service->getDepartments());
    }
    
    public function createDepartment(Request $request) 
    {   
        guard(config('permissions.department-create'));
        
        $validator = Validator::make($request->all(), DepartmentRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeDepartment($validator->validated())
        );
    }

    public function updateDepartment(Request $request, string $id) 
    {   
        guard(config('permissions.department-update'));
        
        $validator = Validator::make($request->all(), DepartmentRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeDepartment($validator->validated(), $id)
        );
    }
}