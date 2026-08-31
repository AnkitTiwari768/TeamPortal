<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;


final class AiCatalogConstants
{

    public const AI_CATALOG_API_BASE_URL = 'http://192.168.2.47/projects/team_portal/api/v1/AiCatalog';

    public const MOBILE_NUMBER_REGEX = '/^[6-9]\d{9}$/';

    public const UDYAM_NUMBER_REGEX = '/^UDYAM-[A-Z]{2}-\d{2}-\d{7}$/';

    public const TOKEN_TTL_DAYS = 365;

    public const JWT_ALGO = 'HS256';

    public const JWT_ISSUER = 'msme-team-portal-aicatalog';

    public const JWT_SECRET_ENV_KEY = 'AI_CATALOG_JWT_SECRET';

    public const RATE_LIMIT_AUTH = '10,1';

    public const RATE_LIMIT_VALIDATE = '60,1';

    /*
     * Udyam government-registry lookup (used by udyamDetails()). Mirrors
     * the endpoint/token already used elsewhere in the app (see
     * App\Domain\Udyam\CheckUdyamCombinationAction and
     * App\Http\Api\V1\Signup\SignupService::getUdyamDetails()) rather than
     * introducing a new one.
     */
    public const UDYAM_API_BASE_URL = 'https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam';

    public const UDYAM_API_TOKEN_CONFIG_KEY = 'constant.UDYAM_TOKEN';

    public const UDYAM_API_TIMEOUT_SECONDS = 15;

    public const MSME_SCHEMES_TABLE = 'team_msme_schemes';

    public const USERS_TABLE = 'users';

    public const API_LOGS_TABLE = 'ai_catalog_api_logs';

    public const RATE_LIMIT_UDYAM_DETAILS = '10,1';
}
