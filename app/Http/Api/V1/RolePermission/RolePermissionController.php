<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\RolePermission;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\RolePermission\RolePermissionValidation as Validation;
use App\Http\Api\V1\RolePermission\RolePermissionService as Service;

class RolePermissionController extends ApiController 
{
    public function __construct(private Service $service) 
    {
        $this->service = $service;
    }

    public function getDetails(string $roleId): JsonResponse
    {

        try 
        {
            return $this->success($this->service->getDetails($roleId));
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }

    public function save(Request $request): JsonResponse 
    {	
       try 
        {
            $validator = Validation::getRules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }

            return $this->updated($this->service->save($validator->validated()));
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }
}