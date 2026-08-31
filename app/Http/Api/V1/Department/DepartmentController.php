<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Department;

use App\Http\Controllers\ApiController;
use App\Http\Api\V1\Department\DepartmentValidation as Validation;
use Illuminate\Http\Request;

class DepartmentController extends ApiController
{
    public function __construct(private DepartmentService $service) {}

    public function storeDepartment(Request $request, ?string $id = null)
    {
        $validator = Validator::make($request->all(), DepartmentRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->service->storeDepartment($validator->validated())
        );
    }

    public function getDepartments()
    {    
        return $this->success($this->service->getDepartments());
    }
}