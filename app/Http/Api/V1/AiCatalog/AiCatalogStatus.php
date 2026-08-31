<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AiCatalog;

final class AiCatalogStatus
{
    public const USER_NOT_FOUND = 'USER_NOT_FOUND';

    public const INVALID_CREDENTIALS = 'INVALID_CREDENTIALS';

    public const TOKEN_EXPIRED = 'TOKEN_EXPIRED';

    public const TOKEN_INVALID = 'TOKEN_INVALID';

    public const INTERNAL_ERROR = 'INTERNAL_ERROR';

    public const UDYAM_API_UNAVAILABLE = 'UDYAM_API_UNAVAILABLE';

    public const UDYAM_PARSE_ERROR = 'UDYAM_PARSE_ERROR';

    public const UDYAM_RECORD_NOT_FOUND = 'UDYAM_RECORD_NOT_FOUND';

    public const UDYAM_SCHEME_ALREADY_EXISTS = 'UDYAM_SCHEME_ALREADY_EXISTS';

    public const MSME_SCHEME_DUPLICATE = 'MSME_SCHEME_DUPLICATE';

    public const UDYAM_SCHEME_SAVE_FAILED = 'UDYAM_SCHEME_SAVE_FAILED';

    // Plain HTTP status codes, kept local so this module has no dependency
    // on a shared status-code class.
    private const HTTP_UNAUTHORIZED = 401;

    private const HTTP_NOT_FOUND = 404;

    private const HTTP_CONFLICT = 409;

    private const HTTP_UNPROCESSABLE_ENTITY = 422;

    private const HTTP_INTERNAL_SERVER_ERROR = 500;

    private const HTTP_BAD_GATEWAY = 502;

    private const MAP = [
        self::USER_NOT_FOUND => [
            self::HTTP_NOT_FOUND,
            'No registered enterprise exists for the given mobile number.',
        ],
        self::INVALID_CREDENTIALS => [
            self::HTTP_UNAUTHORIZED,
            'The mobile number and Udyam registration number do not match.',
        ],
        self::TOKEN_EXPIRED => [
            self::HTTP_UNAUTHORIZED,
            'Your session has expired. Please log in again.',
        ],
        self::TOKEN_INVALID => [
            self::HTTP_UNAUTHORIZED,
            'The provided token is malformed or invalid.',
        ],
        self::INTERNAL_ERROR => [
            self::HTTP_INTERNAL_SERVER_ERROR,
            'An unexpected error occurred. Please try again later.',
        ],
        self::UDYAM_API_UNAVAILABLE => [
            self::HTTP_BAD_GATEWAY,
            'The Udyam registry could not be reached. Please try again later.',
        ],
        self::UDYAM_PARSE_ERROR => [
            self::HTTP_BAD_GATEWAY,
            'The Udyam registry returned an unreadable response. Please try again later.',
        ],
        self::UDYAM_RECORD_NOT_FOUND => [
            self::HTTP_UNPROCESSABLE_ENTITY,
            'No Udyam registry record was found for the given Udyam registration number and mobile number.',
        ],
        self::UDYAM_SCHEME_ALREADY_EXISTS => [
            self::HTTP_CONFLICT,
            'An MSME scheme record already exists for this Udyam registration number.',
        ],
        self::MSME_SCHEME_DUPLICATE => [
            self::HTTP_CONFLICT,
            'An MSME scheme record already exists for this mobile number or Udyam registration number.',
        ],
        self::UDYAM_SCHEME_SAVE_FAILED => [
            self::HTTP_INTERNAL_SERVER_ERROR,
            'Udyam details were fetched but could not be saved. Please try again later.',
        ],
    ];

    // Returns the HTTP status code registered for a given AiCatalogStatus code.
    public static function httpStatusFor(string $code): int
    {
        return self::MAP[$code][0] ?? self::HTTP_INTERNAL_SERVER_ERROR;
    }

    // Returns the default human-readable message registered for a given AiCatalogStatus code.
    public static function messageFor(string $code): string
    {
        return self::MAP[$code][1] ?? self::MAP[self::INTERNAL_ERROR][1];
    }
}
