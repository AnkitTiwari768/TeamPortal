<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;
use App\Web\ApplicationWorkflow\WorkflowType;
use Carbon\Carbon;
use App\Traits\HasFileUpload;

class ApproveBatchWorkflow
{
    use HasUser, HasFileUpload, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $status = ClaimReviewStatus::APPROVED;

            $userId = auth()->id();

            $fromRoleId = $authRoleId = $this->fetchRoleId($userId);

            $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);

            $nextWorkflow = $this->batchWorkflowService->getNextBatchWorkflow($userId, $workflowTypeId);

            $batchWorkflow = [];

            foreach ($nextWorkflow as $workflow) {
                $batchWorkflow[] = [
                    'id' => uuid(),
                    'batch_id' => $data['batch_id'],
                    'workflow_id' => $workflow->id,
                    'workflow_level' => $workflow->level,
                    'from_role_id' => $fromRoleId,
                    'from_user_id' => $userId,
                    'to_role_id' => $workflow->role_id,
                    'to_user_id' => $workflow->user_id ?? $this->batchWorkflowService->getBatchCreatedUserId($data['batch_id']),
                    'comments' => $data['comments'] ?? null,
                    'status_id' => $status->value,
                    'created_at' => Carbon::now(),
                    'created_by' => $userId
                ];
            }

            if ($batchWorkflow) {
                DB::table('batch_workflow')->insert($batchWorkflow);
            }

            $updatedBatchReviewStatus = [
                'batch_id' => $data['batch_id'],
                'status_id' => $status->value,
                'updated_at' => Carbon::now(),
                'updated_by' => $userId
            ];

            DB::table('batch_review_statuses')
                ->where('batch_id', $data['batch_id'])
                ->where('role_id', $authRoleId)
                ->where('user_id', $userId)
                ->update($updatedBatchReviewStatus);

            if (hasRole('ca')) {
                DB::table('batch_documents')->insert([
                    'id' => uuid(),
                    'batch_id' => $data['batch_id'],
                    'document_category_id' => $data['document_category_id'],
                    'file_upload_id' => static::getFileUploadIdBySystemName($data['file_upload_id']),
                    'status' => true,
                    'uploaded_at' => Carbon::now(),
                    'uploaded_by' => $userId
                ]);

                $batchClaims = DB::table('batch_claims')->where('batch_id', $data['batch_id'])->pluck('claim_id')->toArray();

                DB::table('batches')->where('id', $data['batch_id'])->update([
                    'is_ca_certified' => true,
                ]);

                DB::table('claims')->whereIn('id', $batchClaims)->update([
                    'ca_review_status' => $status->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => authId(),
                ]);
            }

            $this->createTimeline([
                'batch_id' => $data['batch_id'],
                'comments' => $data['comments'],
                'status' => 'approved',
                'is_shown_to_ca' => true,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $data['batch_id'],
                    claimIds: $batchClaims,
                    status: 'approved'
                )
            ]);
        });
    }
}
