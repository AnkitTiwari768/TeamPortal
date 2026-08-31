<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

use DateTime;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;


final class AiCatalogService
{
    public function __construct(private readonly AiCatalogRepository $repository)
    {
    }

    public function register(string $mobileNumber, string $udyamNumber): array
    {
        try {
            if ($this->repository->msmeSchemeDuplicateExists($udyamNumber, $mobileNumber)) {
                return $this->failure(AiCatalogStatus::MSME_SCHEME_DUPLICATE);
            }

            $lookup = $this->getUdyamDetails($udyamNumber, $mobileNumber);

            if ($lookup['status'] !== 'valid') {
                return $this->failure($this->udyamFailureCode($lookup['status']));
            }

            $schemeData = $this->buildMsmeSchemeData($mobileNumber, $lookup['basic_detail'], $lookup['activity_detail']);

            if ($schemeData === null) {
                return $this->failure(AiCatalogStatus::UDYAM_PARSE_ERROR);
            }

            $userId = (string) Str::orderedUuid();
            $userData = $this->buildUserData($userId, $mobileNumber, $lookup['basic_detail']);
            $schemeData['user_id'] = $userId;

            try {
                DB::transaction(function () use ($userData, $schemeData): void {
                    $this->repository->insertUser($userData);
                    $this->repository->insertMsmeScheme($schemeData);
                });
            } catch (QueryException $e) {
                $this->logException('AiCatalog registration save failed', $e);

                return $this->failure(AiCatalogStatus::UDYAM_SCHEME_SAVE_FAILED);
            }

            $issued = $this->issueToken($schemeData['id']);

            return $this->success([
                'status' => 'SUCCESS',
                'user_id' => $userId,
                'message' => 'Registration successful. You are now logged in.',
                'session_token' => $issued['token'],
                'token_expiry' => $issued['expires_at']->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            $this->logException('AiCatalog registration failed', $e);

            return $this->failure(AiCatalogStatus::INTERNAL_ERROR);
        }
    }


    public function login(string $mobileNumber, string $udyamNumber): array
    {
        try {
            $scheme = $this->repository->findMsmeSchemeByMobile($mobileNumber);

            if (! $scheme) {
                return $this->failure(AiCatalogStatus::USER_NOT_FOUND);
            }

            if (! hash_equals((string) $scheme->udyam_no, $udyamNumber)) {
                return $this->failure(AiCatalogStatus::INVALID_CREDENTIALS);
            }

            $issued = $this->issueToken((string) $scheme->id);

            return $this->success([
                'status' => 'SUCCESS',
                'user_id' => $scheme->user_id,
                'session_token' => $issued['token'],
                'token_type' => 'Bearer',
                'issued_at' => $issued['issued_at']->toIso8601String(),
                'expires_at' => $issued['expires_at']->toIso8601String(),
                'profile' => [
                    'mobile_number' => $scheme->mobile,
                    'udyam_registration_number' => $scheme->udyam_no,
                ],
            ]);
        } catch (Throwable $e) {
            $this->logException('AiCatalog login failed', $e);

            return $this->failure(AiCatalogStatus::INTERNAL_ERROR);
        }
    }


    public function validateToken(string $token): array
    {
        try {
            try {
                $decoded = JWT::decode($token, new Key($this->jwtSecret(), AiCatalogConstants::JWT_ALGO));
            } catch (ExpiredException) {
                return $this->failure(AiCatalogStatus::TOKEN_EXPIRED);
            } catch (Throwable) {
                return $this->failure(AiCatalogStatus::TOKEN_INVALID);
            }

            $subjectId = $decoded->sub ?? null;
            $expiresAtTimestamp = $decoded->exp ?? null;

            if (! is_string($subjectId) || $subjectId === '' || ! is_numeric($expiresAtTimestamp)) {
                return $this->failure(AiCatalogStatus::TOKEN_INVALID);
            }

            $userId = $this->repository->findUserIdBySchemeId($subjectId);

            if ($userId === null) {
                return $this->failure(AiCatalogStatus::TOKEN_INVALID);
            }

            return $this->success([
                'status' => 'VALID',
                'user_id' => $userId,
                'expires_at' => Carbon::createFromTimestamp((int) $expiresAtTimestamp)->toIso8601String(),
            ]);
        } catch (Throwable $e) {
            $this->logException('AiCatalog token validation failed', $e);

            return $this->failure(AiCatalogStatus::INTERNAL_ERROR);
        }
    }



    public function logApiRequest(Request $request, JsonResponse $response): void
    {
        try {
            $statusCode = $response->getStatusCode();
            $isSuccess = $statusCode < 400;
            $decoded = json_decode((string) $response->getContent(), true);

            $this->repository->insertApiLog([
                'id' => (string) Str::orderedUuid(),
                'endpoint' => $this->resolveEndpointUrl($request),
                'method' => $request->method(),
                'mobile_number' => $request->input('mobile_number'),
                'udyam_registration_number' => $request->input('udyam_registration_number'),
                'status_code' => $statusCode,
                'is_success' => $isSuccess,
                'error_code' => $isSuccess ? null : ($decoded['code'] ?? null),
                'error_message' => $isSuccess ? null : ($decoded['message'] ?? null),
                'response_body' => (string) $response->getContent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (Throwable $e) {
            $this->logException('AiCatalog API log insert failed', $e);
        }
    }

    // Builds the full, absolute URL for a request from the centrally-defined AiCatalog base URL.
    private function resolveEndpointUrl(Request $request): string
    {
        $action = Str::after($request->path(), 'v1/AiCatalog/');

        return AiCatalogConstants::AI_CATALOG_API_BASE_URL.'/'.$action;
    }


    private function udyamFailureCode(string $lookupStatus): string
    {
        return match ($lookupStatus) {
            'not_found' => AiCatalogStatus::UDYAM_RECORD_NOT_FOUND,
            'parse_error' => AiCatalogStatus::UDYAM_PARSE_ERROR,
            default => AiCatalogStatus::UDYAM_API_UNAVAILABLE,
        };
    }

    /**
     * Maps a valid getUdyamDetails() result into a team_msme_schemes column array.
     *
     * Uses the same column set as the existing Signup insertion flow
     * (see App\Http\Api\V1\Signup\SignupService::store()), plus
     * is_from_aicatalog so records created through this module can be
     * told apart from ones created by that flow. Shared by register() and
     * fetchAndStoreUdyamDetails() so the mapping is defined exactly once.
     *
     * @return array|null null if IncorporationDate can't be parsed —
     *                     callers should treat that as UDYAM_PARSE_ERROR.
     */
    private function buildMsmeSchemeData(string $mobileNumber, SimpleXMLElement $basicDetail, ?SimpleXMLElement $activityDetail): ?array
    {
        $incorporationDate = DateTime::createFromFormat('m/d/Y', (string) ($this->xmlNodeText($basicDetail->IncorporationDate ?? null) ?? ''));

        if ($incorporationDate === false) {
            return null;
        }

        return [
            'id' => (string) Str::orderedUuid(),
            'team_id' => getNextTeamId() ?: null,
            'udyam_no' => trim((string) $basicDetail->UdyamNo),
            'mobile' => $mobileNumber,
            'email' => $this->xmlNodeText($basicDetail->EmailId ?? null),
            'enterprise_name' => $this->xmlNodeText($basicDetail->EnterpriseName ?? null),
            'entrepreneur_name' => $this->xmlNodeText($basicDetail->EntrepreneurName ?? null),
            'organisation_type' => $this->xmlNodeText($basicDetail->OrganisationType ?? null),
            'address' => $this->xmlNodeText($basicDetail->CommunicationAddress ?? null),
            'pincode' => $this->xmlNodeText($basicDetail->PINCode ?? null),
            'ph' => $this->xmlNodeText($basicDetail->PH ?? null),
            'major_activity' => $this->xmlNodeText($basicDetail->MajorActivity ?? null),
            'msme_classification' => $this->xmlNodeText($basicDetail->EnterpriseType ?? null),
            'gender' => $this->xmlNodeText($basicDetail->Gender ?? null),
            'social_category' => $this->xmlNodeText($basicDetail->SocialCategory ?? null),
            'incorporation_date' => $incorporationDate->format('Y-m-d'),
            'total_emp' => $this->xmlNodeText($basicDetail->TotalEmp ?? null),
            'state_id' => $this->repository->findLocationId('states', $this->xmlNodeText($basicDetail->LG_ST_Code ?? null) ?? ''),
            'district_id' => $this->repository->findLocationId('locations', $this->xmlNodeText($basicDetail->LG_DT_Code ?? null) ?? ''),
            'enterprise_details' => $this->xmlToJson($basicDetail->EnterpriseDetail ?? null),
            'activity_details' => $this->xmlToJson($activityDetail),
            'is_from_aicatalog' => 1,
            'status' => 1,
            'created_at' => currentDateTime(),
            'updated_at' => currentDateTime(),
        ];
    }

    private function buildUserData(string $userId, string $mobileNumber, SimpleXMLElement $basicDetail): array
    {
        $email = $this->xmlNodeText($basicDetail->EmailId ?? null);

        return [
            'id' => $userId,
            'username' => $email,
            'first_name' => $this->xmlNodeText($basicDetail->EnterpriseName ?? null),
            'mobile' => $mobileNumber,
            'email' => $email,
            'status' => 1,
            'is_msme' => 1,
            'created_at' => currentDateTime(),
        ];
    }

    /**
     * Calls the Udyam registry for the given number/mobile and returns a discriminated lookup result.
     *
     * @return array{status: 'valid', basic_detail: \SimpleXMLElement, activity_detail: \SimpleXMLElement|null}
     *       | array{status: 'not_found'|'parse_error'|'api_unavailable'}
     */
    private function getUdyamDetails(string $udyamNumber, string $mobileNumber): array
    {
        try {
            $response = Http::withoutVerifying()
                ->timeout(AiCatalogConstants::UDYAM_API_TIMEOUT_SECONDS)
                ->get(sprintf(
                    '%s/%s,%s,%s',
                    AiCatalogConstants::UDYAM_API_BASE_URL,
                    $udyamNumber,
                    $mobileNumber,
                    (string) config(AiCatalogConstants::UDYAM_API_TOKEN_CONFIG_KEY)
                ));
        } catch (Throwable $e) {
            $this->logException('AiCatalog Udyam API request failed', $e);

            return ['status' => 'api_unavailable'];
        }

        if (! $response->successful() || $response->body() === '') {
            return ['status' => 'api_unavailable'];
        }

        // @-suppressed: simplexml_load_string() emits E_WARNING per
        // malformed node on top of returning false; the false-check below
        // is what we actually act on.
        $xmlObject = @simplexml_load_string($response->body());

        if ($xmlObject === false) {
            return ['status' => 'parse_error'];
        }

        $basicDetail = $xmlObject->BasicDetail ?? null;

        if ($basicDetail === null) {
            return ['status' => 'parse_error'];
        }

        $resolvedUdyamNo = trim((string) ($basicDetail->UdyamNo ?? ''));
        $apiError = trim((string) ($basicDetail->Error ?? ''));
        // ErrorCode has been seen on either BasicDetail or the document
        // root depending on the failure the registry is reporting.
        $errorCode = trim((string) ($basicDetail->ErrorCode ?? $xmlObject->ErrorCode ?? ''));

        if (
            $resolvedUdyamNo === ''
            || strtoupper($resolvedUdyamNo) === 'NO'
            || $apiError !== ''
            || $errorCode === '1'
        ) {
            return ['status' => 'not_found'];
        }

        return [
            'status' => 'valid',
            'basic_detail' => $basicDetail,
            'activity_detail' => $xmlObject->ActivityDetail ?? null,
        ];
    }


    private function xmlNodeText(mixed $node): ?string
    {
        if ($node === null) {
            return null;
        }

        $value = trim((string) $node);

        return $value !== '' ? $value : null;
    }


    private function xmlToJson(mixed $node): string
    {
        if ($node === null) {
            return (string) json_encode(null);
        }

        return (string) json_encode(json_decode((string) json_encode($node), true));
    }

    /**
     * Issues a self-contained JWT — no persistence, nothing to revoke.
     * $subjectId is the team_msme_schemes row id.
     *
     * @return array{token: string, issued_at: Carbon, expires_at: Carbon}
     */
    private function issueToken(string $subjectId): array
    {
        $jti = (string) Str::orderedUuid();
        $issuedAt = Carbon::now();
        $expiresAt = $issuedAt->copy()->addDays(AiCatalogConstants::TOKEN_TTL_DAYS);

        $token = JWT::encode([
            'iss' => AiCatalogConstants::JWT_ISSUER,
            'sub' => $subjectId,
            'jti' => $jti,
            'iat' => $issuedAt->timestamp,
            'exp' => $expiresAt->timestamp,
        ], $this->jwtSecret(), AiCatalogConstants::JWT_ALGO);

        return ['token' => $token, 'issued_at' => $issuedAt, 'expires_at' => $expiresAt];
    }

    // Returns the JWT signing secret, falling back to APP_KEY if a dedicated one isn't configured.
    private function jwtSecret(): string
    {
        return (string) env(AiCatalogConstants::JWT_SECRET_ENV_KEY, config('app.key'));
    }

    // Wraps a successful result payload in the internal {success, data} shape.
    private function success(array $data): array
    {
        return ['success' => true, 'data' => $data];
    }

    // Wraps a failure error code in the internal {success, error_code} shape.
    private function failure(string $errorCode): array
    {
        return ['success' => false, 'error_code' => $errorCode];
    }

    // Logs an exception with its context message for later debugging.
    private function logException(string $context, Throwable $e): void
    {
        Log::error("{$context}: ".$e->getMessage(), ['exception' => $e]);
    }
}
