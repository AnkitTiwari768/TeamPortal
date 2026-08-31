<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

/**
 * The MSME bulk-upload/draft-processing flow only ever tracks three states — the same
 * three as MseDraftBulkUploadService::STATUS_PENDING / STATUS_MIGRATED / STATUS_FAILED on
 * team_msme_scheme_drafts. This single enum backs BOTH the run-level log
 * (team_msme_bulk_upload_logs.status) and the per-MSME detail log
 * (team_msme_bulk_upload_log_details.status), so the whole logging system speaks the
 * same three words everywhere — no Started/Completed/Processing/etc.
 */
enum MseBulkUploadStatus: string
{
    case Pending  = 'Pending';
    case Migrated = 'Migrated';
    case Failed   = 'Failed';
}
