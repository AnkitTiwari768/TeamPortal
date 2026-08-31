<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

final class AiCatalogController extends Controller
{
    public function __construct(private readonly AiCatalogService $service)
    {
    }


    public function registration(AiCatalogRequest $request): JsonResponse
    {
        $result = $this->service->register(
            (string) $request->validated('mobile_number'),
            (string) $request->validated('udyam_registration_number'),
        );

        $response = $result['success']
            ? response()->json(AiCatalogResource::make($result['data']), 201)
            : $this->failureResponse($result['error_code']);

        return $this->logAndRespond($request, $response);
    }


    public function login(AiCatalogRequest $request): JsonResponse
    {
        $result = $this->service->login(
            (string) $request->validated('mobile_number'),
            (string) $request->validated('udyam_registration_number'),
        );

        $response = $result['success']
            ? response()->json(AiCatalogResource::make($result['data']), 200)
            : $this->failureResponse($result['error_code']);

        return $this->logAndRespond($request, $response);
    }


    public function validateToken(AiCatalogRequest $request): JsonResponse
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->logAndRespond($request, $this->failureResponse(AiCatalogStatus::TOKEN_INVALID));
        }

        $result = $this->service->validateToken($token);

        $response = $result['success']
            ? response()->json(AiCatalogResource::make($result['data']), 200)
            : $this->failureResponse($result['error_code']);

        return $this->logAndRespond($request, $response);
    }

    private function failureResponse(string $code): JsonResponse
    {
        return response()->json([
                'message' => AiCatalogStatus::messageFor($code),
                'code' => $code,
            ], AiCatalogStatus::httpStatusFor($code));
    }


    private function logAndRespond(Request $request, JsonResponse $response): JsonResponse
    {
        $this->service->logApiRequest($request, $response);
        return $response;
    }
}
