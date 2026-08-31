<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;
use App\Web\ApplicationWorkflow\WorkflowType;
use App\Traits\HasFileUpload;
use Carbon\Carbon;

class BatchProcessByCA
{
    use HasUser, HasFileUpload, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $userId = auth()->id();

            $fromRoleId = $authRoleId = $this->fetchRoleId($userId);

            $currentWorkflow = $this->batchWorkflowService->getCurrentBatchWorkflowLevel($data['batch_id']);

            $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);

            $batchWorkflow = [];

            $createdBy = $this->batchWorkflowService->getBatchCreatedUserId($data['batch_id']);

            $revertedBatchClaims = DB::table('batch_claims')
                ->where('batch_id', $data['batch_id'])
                ->where('ca_review_status', ClaimReviewStatus::REVERTED->value)
                ->pluck('claim_id')
                ->toArray();


            $approvedBatchClaims = DB::table('batch_claims')
                ->where('batch_id', $data['batch_id'])
                ->where('ca_review_status', ClaimReviewStatus::APPROVED->value)
                ->pluck('claim_id')
                ->toArray();

            $statusId = '';
            $statusName = '';

            if ($approvedBatchClaims) {
                $statusId = ClaimReviewStatus::APPROVED->value;
                $statusName = 'approved';
            }

            if ($revertedBatchClaims && !$approvedBatchClaims) {
                $statusId = ClaimReviewStatus::REVERTED->value;
                $statusName = 'reverted';
            }

            if ($approvedBatchClaims && $revertedBatchClaims) {
                $statusId = ClaimReviewStatus::APPROVED->value;
                $statusName = 'approved';
            }


            $batchWorkflow[] = [
                'id' => uuid(),
                'batch_id' => $data['batch_id'],
                'workflow_id' => $currentWorkflow->id,
                'workflow_level' => $currentWorkflow->workflow_level,
                'from_role_id' => $fromRoleId,
                'from_user_id' => $userId,
                'to_role_id' => $this->fetchRoleId($createdBy),
                'to_user_id' => $createdBy,
                'comments' => $data['comments'] ?? null,
                'status_id' => $statusId,
                'created_at' => Carbon::now(),
                'created_by' => $userId
            ];

            if ($batchWorkflow) {
                DB::table('batch_workflow')->insert($batchWorkflow);
            }

            $updatedBatchReviewStatus = [
                'batch_id' => $data['batch_id'],
                'status_id' => $statusId,
                'updated_at' => Carbon::now(),
                'updated_by' => $userId
            ];

            DB::table('batch_review_statuses')
                ->where('batch_id', $data['batch_id'])
                ->where('role_id', $authRoleId)
                ->where('user_id', $userId)
                ->update($updatedBatchReviewStatus);

            $batchData = [];

            if ($approvedBatchClaims) {
                $batchData += [
                    'is_ca_certified' => true
                ];
            }

            if ($revertedBatchClaims) {
                $batchData += [
                    'is_reverted_by_ca' => true
                ];
            }

            if ($revertedBatchClaims && !$approvedBatchClaims) {
                $batchData += [
                    'status_id' => ClaimReviewStatus::REVERTED->value,
                ];
            }

            DB::table('batches')->where('id', $data['batch_id'])->update($batchData);


            if ($revertedBatchClaims) {
                DB::table('claims')->whereIn('id', $revertedBatchClaims)->update([
                    'status' => ClaimReviewStatus::REVERTED->value,
                    'review_status' => ClaimReviewStatus::REVERTED->value,
                ]);

                DB::table('batch_claims')
                    ->where('batch_id', $data['batch_id'])
                    ->whereIn('claim_id', $revertedBatchClaims)
                    ->update([
                        'status_id' => ClaimReviewStatus::REVERTED->value,
                        'is_deleted' => true,
                        'is_revert_by_ca' => true
                    ]);
            }


            if ($approvedBatchClaims) {
                DB::table('claims')->whereIn('id', $approvedBatchClaims)->update([
                    'ca_review_status' => ClaimReviewStatus::APPROVED->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => authId(),
                ]);

                DB::table('batch_claims')
                    ->where('batch_id', $data['batch_id'])
                    ->whereIn('claim_id', $approvedBatchClaims)
                    ->update([
                        'ca_review_status' => ClaimReviewStatus::APPROVED->value,
                        'ca_review_status_updated_at' => now(),
                        'ca_review_status_updated_by' => authId(),
                    ]);


                DB::table('batch_documents')->insert([
                    'id' => uuid(),
                    'batch_id' => $data['batch_id'],
                    'document_category_id' => $data['document_category_id'],
                    'file_upload_id' => static::getFileUploadIdBySystemName($data['file_upload_id']),
                    'status' => true,
                    'uploaded_at' => Carbon::now(),
                    'uploaded_by' => $userId
                ]);

                DB::table('claims')->whereIn('id', $approvedBatchClaims)->update([
                    'ca_review_status' => ClaimReviewStatus::APPROVED->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => authId(),
                ]);
            }


            $this->createTimeline([
                'batch_id' => $data['batch_id'],
                'comments' => $data['comments'],
                'status' => $statusName,
                'is_shown_to_ca' => true,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $data['batch_id'],
                    claimIds: [...$revertedBatchClaims, ...$approvedBatchClaims]
                )
            ]);
        });
    }
}
