<?php

declare(strict_types=1);

namespace App\Web\MseBulkRegistration\Logging;

use Illuminate\Support\Facades\Log;

/**
 * Logging facade for the MSME bulk-upload/draft-processing flow (manual upload + both the
 * SNP and IA pending-draft crons). Every public method here is defensive on purpose: a
 * logging failure (bad connection, full disk, whatever) must never surface as a failure of
 * the real import/migration flow, so every DB call is wrapped and only reported to \Log.
 *
 * Usage is the same for all three callers:
 *   $context = $logService->startRun(MseBulkUploadRunType::ManualUpload, ...);
 *   ... existing business logic, untouched ...
 *   $logService->logDetail($context, [...]) / logDetails($context, [...]) per MSME row
 *   $logService->completeRun($context, [...counts...]) or ->failRun($context, $message)
 */
class MseBulkUploadLogService
{
    public function __construct(private readonly MseBulkUploadLogRepository $repository)
    {
    }

    /**
     * @param array{file_name?: string, file_size?: int, file_extension?: string}|null $fileDetails
     */
    public function startRun(
        MseBulkUploadRunType $type,
        ?int $roleType = null,
        ?string $createdBy = null,
        ?array $fileDetails = null,
        ?string $batchId = null
    ): MseBulkUploadLogContext {
        $context = new MseBulkUploadLogContext(
            id: uuid(),
            type: $type,
            roleType: $roleType ?? $type->defaultRoleType(),
            startedAtMs: $this->nowMs(),
        );

        try {
            $this->repository->createRun([
                'id' => $context->id,
                'type' => $type->value,
                'role_type' => $context->roleType,
                'status' => MseBulkUploadStatus::Pending->value,
                'batch_id' => $batchId,
                'file_name' => $fileDetails['file_name'] ?? null,
                'file_size' => $fileDetails['file_size'] ?? null,
                'file_extension' => $fileDetails['file_extension'] ?? null,
                'created_by' => $createdBy,
                'started_at' => now(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('MSME bulk upload log: failed to start run', [
                'run_id' => $context->id,
                'type' => $type->value,
                'error' => $e->getMessage(),
            ]);
        }

        return $context;
    }

    /**
     * @param array{
     *   total_processed?: int, success_count?: int, failed_count?: int, retry_count?: int,
     *   skipped_count?: int, reconciled_count?: int, pending_count?: int
     * } $stats
     */
    /**
     * Marks the run Migrated — i.e. it ran to completion. Per-MSME outcomes (including any
     * failures) live on the detail rows via logDetail()/logDetails(); this only reflects
     * whether the run itself finished, matching the Pending -> Migrated / Failed lifecycle.
     */
    public function completeRun(MseBulkUploadLogContext $context, array $stats, ?string $batchId = null): void
    {
        try {
            $attributes = array_filter([
                'status' => MseBulkUploadStatus::Migrated->value,
                'total_processed' => $stats['total_processed'] ?? null,
                'success_count' => $stats['success_count'] ?? null,
                'failed_count' => $stats['failed_count'] ?? null,
                'retry_count' => $stats['retry_count'] ?? null,
                'skipped_count' => $stats['skipped_count'] ?? null,
                'reconciled_count' => $stats['reconciled_count'] ?? null,
                'pending_count' => $stats['pending_count'] ?? null,
                'batch_id' => $batchId,
            ], static fn ($value) => $value !== null);

            $attributes['completed_at'] = now();
            $attributes['duration_ms'] = $this->nowMs() - $context->startedAtMs;
            $attributes['updated_at'] = now();

            $this->repository->updateRun($context->id, $attributes);
        } catch (\Throwable $e) {
            Log::error('MSME bulk upload log: failed to complete run', [
                'run_id' => $context->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failRun(MseBulkUploadLogContext $context, string $errorMessage): void
    {
        try {
            $this->repository->updateRun($context->id, [
                'status' => MseBulkUploadStatus::Failed->value,
                'error_message' => $errorMessage,
                'completed_at' => now(),
                'duration_ms' => $this->nowMs() - $context->startedAtMs,
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('MSME bulk upload log: failed to mark run failed', [
                'run_id' => $context->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Keeps a manual-upload run's status synchronized with the actual state of the drafts
     * it created in team_msme_scheme_drafts: Pending while any of them is still Pending,
     * Failed once none are left Pending but at least one ended up Failed, and Migrated only
     * once every draft in the batch migrated. Always recomputed from the drafts table rather
     * than trusted from a prior write, so it is safe to call this repeatedly — once right
     * after the upload itself, and again from the cron every time it resolves a draft in
     * this batch.
     */
    public function syncBatchStatus(?string $batchId, ?string $createdBy = null): void
    {
        if (!$batchId) {
            return;
        }

        try {
            $counts = $this->repository->draftStatusCounts($batchId, $createdBy);

            if ($counts['total'] === 0) {
                return;
            }

            $status = match (true) {
                $counts['pending'] > 0 => MseBulkUploadStatus::Pending,
                $counts['failed'] > 0 => MseBulkUploadStatus::Failed,
                default => MseBulkUploadStatus::Migrated,
            };

            $attributes = [
                'status' => $status->value,
                'updated_at' => now(),
            ];

            if ($status !== MseBulkUploadStatus::Pending) {
                $attributes['completed_at'] = now();
            }

            $this->repository->updateRunsByBatch($batchId, $createdBy, $attributes);
        } catch (\Throwable $e) {
            Log::error('MSME bulk upload log: failed to sync batch status from drafts', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param array{
     *   udyam_no?: ?string, mobile?: ?string, role_type?: ?int,
     *   status: MseBulkUploadStatus, attempt_number?: ?int,
     *   dependency_details?: ?array, error_message?: ?string,
     *   reference_table?: ?string, reference_id?: ?string
     * } $detail
     */
    public function logDetail(MseBulkUploadLogContext $context, array $detail): void
    {
        $this->logDetails($context, [$detail]);
    }

    /**
     * @param array<int, array<string, mixed>> $details
     */
    public function logDetails(MseBulkUploadLogContext $context, array $details): void
    {
        if (empty($details)) {
            return;
        }

        try {
            $rows = [];

            foreach ($details as $detail) {
                $status = $detail['status'] ?? null;
                $status = $status instanceof MseBulkUploadStatus ? $status->value : (string) $status;

                $rows[] = [
                    'id' => uuid(),
                    'log_id' => $context->id,
                    'udyam_no' => $detail['udyam_no'] ?? null,
                    'mobile' => isset($detail['mobile']) ? (string) $detail['mobile'] : null,
                    'role_type' => $detail['role_type'] ?? $context->roleType,
                    'status' => $status,
                    'attempt_number' => $detail['attempt_number'] ?? null,
                    'dependency_details' => isset($detail['dependency_details'])
                        ? json_encode($detail['dependency_details'])
                        : null,
                    'error_message' => $detail['error_message'] ?? null,
                    'reference_table' => $detail['reference_table'] ?? null,
                    'reference_id' => $detail['reference_id'] ?? null,
                    'created_at' => now(),
                ];
            }

            $this->repository->insertDetails($rows);
        } catch (\Throwable $e) {
            Log::error('MSME bulk upload log: failed to write detail rows', [
                'run_id' => $context->id,
                'count' => count($details),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function nowMs(): int
    {
        return (int) (microtime(true) * 1000);
    }
}
