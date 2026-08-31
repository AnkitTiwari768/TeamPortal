<?php declare(strict_types=1);

namespace App\Http\Api\V1\ModulePermissionTree;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\ApiController;
use App\Http\Api\V1\ModulePermissionTree\ModulePermissionTreeService as Service;

class ModulePermissionTreeController extends ApiController 
{
    public function __construct(protected Service $service) 
    {
        $this->service = $service;
    }

    public function getModulePermissionTree(string $roleId): JsonResponse
    {
        try 
        {
            return $this->success($this->service->getModulePermissionTree($roleId));
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }

    public function getUserPermissionTree(string $userId): JsonResponse
    {
        try 
        {
            return $this->success($this->service->getUserPermissionTree($userId));
        }
        catch (\Throwable $e)
        {
            return $this->handleException($e);
        }
    }
}
