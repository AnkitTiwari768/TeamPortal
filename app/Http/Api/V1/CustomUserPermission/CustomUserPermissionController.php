<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

final class CustomUserPermissionController extends ApiController 
{
    public function __construct(private CustomUserPermissionService $userPermissionService)
    {
        
    }

    public function getUserPermissions(string $userId)
    {
        $result = $this->userPermissionService->getUserPermissions($userId);
        return $result->status() ? $this->success($result->data()) : $this->error($result->message());
    }

    public function storeUserPermissions(Request $request)
    {
        $validationResult = CustomUserPermissionRequest::validateRequest($request);
        if (! $validationResult->status()) return $this->error($validationResult->errors());
        
        $result = $this->userPermissionService->storeUserPermissions(CustomUserPermissionDto::fromRequest($validationResult->validated()));
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->created();
    }

}