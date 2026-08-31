<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

enum MseBulkUploadRunType: string
{
    case CronSnp = 'cron_snp';
    case CronIa = 'cron_ia';
    case ManualUpload = 'manual_upload';

    /**
     * Default role_type for this run type. Manual uploads have none by default —
     * the caller resolves it from the uploading user's active role instead.
     */
    public function defaultRoleType(): ?int
    {
        return match ($this) {
            self::CronSnp => 1,
            self::CronIa => 2,
            self::ManualUpload => null,
        };
    }
}
