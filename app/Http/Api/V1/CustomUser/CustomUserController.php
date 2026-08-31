<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

final class CustomUserController extends ApiController 
{
    public function __construct(private CustomUserService $userService)
    {
        
    }

    public function createUser(Request $request)
    {
        $validationResult = CustomUserRequest::validateRequest($request);
        if (! $validationResult->status()) return $this->error($validationResult->errors());
        
        $result = $this->userService->createUser(CustomUserDto::fromRequest($validationResult->validated()));
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->created();
    }

    public function updateUser(Request $request, string $userId)
    {
        $validationResult = CustomUserRequest::validateRequest($request, $userId);
        if (! $validationResult->status()) return $this->error($validationResult->errors());
        
        $result = $this->userService->updateUser(CustomUserDto::fromRequest($validationResult->validated()), $userId);
        return (! $result->status()) 
            ? $this->error($result->message())
            : $this->updated();
    }

    public function listUser()
    {
        $result = $this->userService->listUser();
        return $result->status() 
            ? $this->success($result->data()) 
            : $this->error($result->message());
    }

    public function getUser(string $userId)
    {
        $result = $this->userService->getUser($userId);
        return $result->status() 
            ? $this->success($result->data()) 
            : $this->error($result->message());
    }
}