<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

/**
 * Handle for one active run, returned by MseBulkUploadLogService::startRun() and threaded
 * through the caller (controller/service/import) so every detail row logged in between can
 * be tied back to the same run id — this is the "batch/run ID" that groups all records from
 * one execution together.
 */
final class MseBulkUploadLogContext
{
    public function __construct(
        public readonly string $id,
        public readonly MseBulkUploadRunType $type,
        public readonly ?int $roleType,
        public readonly int $startedAtMs,
    ) {
    }
}
