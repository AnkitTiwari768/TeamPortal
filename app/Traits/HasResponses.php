<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


trait HasResponses
{
    public function success(?string $message = null, array|AnonymousResourceCollection|null $data = null): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message ?? __('common.success'), 'data' => $data], 200);
    }

    public function successWithCsrf(?string $message = null, ?array $data = []): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message ?? __('common.success'),
            'data' =>
            ['csrf_token' => csrf_token(), ...$data]
        ], 200);
    }

    public function created(?string $message = null, ?array $data = null): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message ?? __('common.created'), 'data' => $data], 201);
    }

    public function updated(?string $message = null, ?array $data = null): JsonResponse
    {
        return response()->json(['status' => true, 'message' => $message ?? __('common.created'), 'data' => $data], 200);
    }

    public function authenticated(): JsonResponse
    {
        return response()->json(['status' => true, 'message' => __('auth.success')], 200);
    }

    public function verified(?string $redirect = null): JsonResponse
    {
        return response()->json(['status' => true, 'message' => __('auth.verified'), 'url' => $redirect], 200);
    }

    public function unauthenticated(): JsonResponse
    {
        return response()->json(['status' => false, 'message' => __('auth.failed')], 401);
    }

    public function expiredOrInvalidOtp(): JsonResponse
    {
        return response()->json(['status' => false, 'message' => __('auth.expired_otp')], 401);
    }

    public function accountBlocked(): JsonResponse
    {
        return response()->json(['status' => false, 'message' => __('auth.account_blocked')], 401);
    }

    public function error(mixed $errors = [], ?string $message = null): JsonResponse
    {
        return response()->json(['status' => false, 'message' => $message ?? __('errors'), 'errors' => $errors], 422);
    }
}
