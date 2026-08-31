<?php

declare(strict_types=1);

namespace App\Http\Api\V1\UserPermission;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;

final class UserPermissionController extends ApiController
{
    public function __construct(
        protected UserPermissionService $userPermissionService
    ) {
    }

    public function store(Request $request)
    {
        try {
            $validator = UserPermissionValidation::validate($request->all());

            if ($validator->fails()) {
                return $this->error($validator->errors());
            }

            $userPermissionDTO = UserPermissionDTO::create(
                $validator->validated()
            );

            $result = $this->userPermissionService->save(
                $userPermissionDTO
            );

            return $this->success($result);

        } catch (\Throwable $e) {
            return $this->handleException($e);
        }
    }
}
