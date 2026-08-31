<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Traits\Respond;
use Illuminate\Http\JsonResponse;

class UserController
{
    use Respond;

    public function create(CreateUserRequest $request, CreateUserAction $action): JsonResponse
    {
        guard(config('permissions.user-create'));

        $dto = CreateUserDTO::fromArray($request->validated());

        $action->execute($dto);

        return $this->created(message: 'User created successfully');
    }

    public function update(string $id, UpdateUserRequest $request, UpdateUserAction $action): JsonResponse
    {
        guard(config('permissions.user-update'));

        $dto = UpdateUserDTO::fromArray($request->validated());

        $action->execute($id, $dto);

        return $this->success(message: 'User updated successfully');
    }

    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        guard(config('permissions.user-update'));

        $action->execute($request->validated('user_id'), $request->validated('password'));

        return $this->success(message: 'Password changed successfully');
    }
}
