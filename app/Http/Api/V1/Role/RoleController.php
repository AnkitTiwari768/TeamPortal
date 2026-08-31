<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Role;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;

class RoleController extends ApiController
{
    public function __construct(private RoleService $roleService) {}

    public function storeRole(Request $request, ?string $id = null)
    {
        $validator = Validator::make($request->all(), RoleRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->created(
            $this->roleService->storeRole($validator->validated())
        );
    }

    public function getRoles()
    {    
        return $this->success($this->roleService->getRoles());
    }
}