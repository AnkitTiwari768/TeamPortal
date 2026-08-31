<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

use Illuminate\Support\Facades\DB;

/**
 * Pure data access for the two MSME bulk-upload log tables. Deliberately has no try/catch
 * of its own — MseBulkUploadLogService is the layer responsible for making sure a logging
 * failure never breaks the real draft/cron processing flow.
 */
class MseBulkUploadLogRepository
{
    public function createRun(array $attributes): void
    {
        MseBulkUploadLog::create($attributes);
    }

    public function updateRun(string $id, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        MseBulkUploadLog::where('id', $id)->update($attributes);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    public function insertDetails(array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('team_msme_bulk_upload_log_details')->insert($chunk);
        }
    }

    /**
     * @return array{total: int, pending: int, migrated: int, failed: int}
     */
    public function draftStatusCounts(string $batchId, ?string $createdBy): array
    {
        $query = DB::table('team_msme_scheme_drafts')->where('batch_id', $batchId);

        if ($createdBy !== null) {
            $query->where('created_by', $createdBy);
        }

        $counts = $query->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'total' => (int) $counts->sum(),
            'pending' => (int) ($counts['Pending'] ?? 0),
            'migrated' => (int) ($counts['Migrated'] ?? 0),
            'failed' => (int) ($counts['Failed'] ?? 0),
        ];
    }

    public function updateRunsByBatch(string $batchId, ?string $createdBy, array $attributes): void
    {
        if (empty($attributes)) {
            return;
        }

        $query = MseBulkUploadLog::where('batch_id', $batchId)
            ->where('type', MseBulkUploadRunType::ManualUpload->value);

        if ($createdBy !== null) {
            $query->where('created_by', $createdBy);
        }

        $query->update($attributes);
    }
}
