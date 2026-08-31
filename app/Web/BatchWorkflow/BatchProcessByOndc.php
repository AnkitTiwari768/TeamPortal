<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use App\Web\ApplicationWorkflow\WorkflowType;
use Illuminate\Support\Facades\DB;

class BatchProcessByOndc
{
    use HasUser, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $authId = auth()->id();

            $fromRoleId = $authRoleId = $this->fetchRoleId($authId);

            $batchId = $data['batch_id'];

            $claims = $this->getBatchClaims($batchId);

            $approvedClaims = $this->getApprovedClaims($claims);

            $revertedAndRejectedClaims = $this->getRevertOrRejectedClaims($claims);

            $updatedBatch = [
                'updated_at' => now(),
                'updated_by' => $authId
            ];

            $hasReverted = $hasRejected = $status = null;

            $revAndRejClaimIds = [];

            if ($revertedAndRejectedClaims) {

                foreach ($revertedAndRejectedClaims as $claim) {
                    if ($claim->ondc_review_status === ClaimReviewStatus::REVERTED->value) {
                        $hasReverted = true;
                    }

                    if ($claim->ondc_review_status === ClaimReviewStatus::REJECTED->value) {
                        $hasRejected = true;
                    }

                    $status = $claim->ondc_review_status;

                    DB::table('batch_claim_histories')
                        ->where('batch_id', $batchId)
                        ->where('claim_id', $claim->id)
                        ->update([
                            'status_id' => $status,
                            'updated_at' => now(),
                            'updated_by' => $authId
                        ]);

                    $revAndRejClaimIds[] = $claim->id;

                    DB::table('claims')->where('id', $claim->id)->update([
                        'status' => $status,
                        'review_status' => $status
                    ]);
                }

                DB::table('batch_claims')
                    ->where('batch_id', $batchId)
                    ->whereIn('claim_id', $revAndRejClaimIds)
                    ->update(['is_deleted' => true, 'deleted_by' => authId()]);


                if ($hasReverted) {
                    $updatedBatch += ['is_reverted_by_ondc' => true];
                }

                if ($hasRejected) {
                    $updatedBatch += ['is_rejected_by_ondc' => true];
                }
            }

            if ($approvedClaims) {
                $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);
                $nextWorkflow = $this->batchWorkflowService->getNextBatchWorkflow($authId, $workflowTypeId);
                $batchWorkflow = [];
                $insertBatchReviewStatus = [];

                $updatedBatch += ['is_sent_nsic' => true];

                foreach ($nextWorkflow as $workflow) {
                    $batchWorkflow[] = [
                        'id' => uuid(),
                        'batch_id' => $data['batch_id'],
                        'workflow_id' => $workflow->id,
                        'workflow_level' => $workflow->level,
                        'from_role_id' => $fromRoleId,
                        'from_user_id' => $authId,
                        'to_role_id' => $workflow->role_id,
                        'to_user_id' => $workflow->user_id ?? $this->batchWorkflowService->getBatchCreatedUserId($data['batch_id']),
                        'comments' => $data['comments'] ?? null,
                        'status_id' => ClaimReviewStatus::APPROVED->value,
                        'created_at' => now(),
                        'created_by' => $authId
                    ];

                    $insertBatchReviewStatus[] = [
                        'id' => uuid(),
                        'batch_id' => $batchId,
                        'role_id' => $workflow->role_id,
                        'user_id' => $workflow->user_id,
                        'status_id' => ClaimReviewStatus::PENDING->value,
                        'created_at' => now(),
                        'created_by' => $authId
                    ];
                }


                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', $authRoleId)
                    ->update(['status_id' => ClaimReviewStatus::APPROVED->value]);
                DB::table('batch_review_statuses')->insert($insertBatchReviewStatus);
                DB::table('batch_workflow')->insert($batchWorkflow);
                DB::table('claims')->whereIn('id', $approvedClaims)->update([
                    'is_sent_nsic' => true,
                    'nsic_review_status' => ClaimReviewStatus::PENDING->value,
                    'nsic_review_status_updated_at' => now(),
                    'nsic_review_status_updated_by' => $authId,
                ]);

                DB::table('batch_claims')->where('batch_id', $batchId)->whereIn('claim_id', $approvedClaims)->update([
                    'nsic_review_status' => ClaimReviewStatus::PENDING->value,
                    'nsic_review_status_updated_at' => now(),
                    'nsic_review_status_updated_by' => $authId,
                ]);
            }

            DB::table('batches')->where('id', $batchId)->update($updatedBatch);

            if (!empty($revertedAndRejectedClaims) && empty($approvedClaims)) {

                foreach ($revertedAndRejectedClaims as $claim) {
                    if ($claim->ondc_review_status === ClaimReviewStatus::REVERTED->value) {
                        $hasReverted = true;
                    }

                    if ($claim->ondc_review_status === ClaimReviewStatus::REJECTED->value) {
                        $hasRejected = true;
                    }
                }
				
                if ($hasReverted == true) {
                    DB::table('batches')->where('id', $batchId)->update([
                        'updated_at' => now(),
                        'updated_by' => authId(),
                        'status_id' => ClaimReviewStatus::REVERTED->value
                    ]);

                    DB::table('batch_review_statuses')
                        ->where('batch_id', $batchId)
                        ->where('role_id', $authRoleId)
                        ->update(['status_id' => ClaimReviewStatus::REVERTED->value]);
                }
				
                if ($hasReverted == false && $hasRejected = true) {
                    DB::table('batches')->where('id', $batchId)->update([
                        'updated_at' => now(),
                        'updated_by' => authId(),
                        'status_id' => ClaimReviewStatus::REJECTED->value
                    ]);

                    DB::table('batch_review_statuses')
                        ->where('batch_id', $batchId)
                        ->where('role_id', $authRoleId)
                        ->update(['status_id' => ClaimReviewStatus::REJECTED->value]);
                }
            }

            $status = '';

            if (!empty($approvedClaims)) {
                $status = 'approved';
            } else {
                foreach ($revertedAndRejectedClaims as $claim) {
                    if ($claim->ondc_review_status === ClaimReviewStatus::REVERTED->value) {
                        $hasReverted = true;
                    }

                    if ($claim->ondc_review_status === ClaimReviewStatus::REJECTED->value) {
                        $hasRejected = true;
                    }
                }

                if ($hasReverted === true) {
                    $status = 'reverted';
                }


                if ($hasReverted === false && $hasRejected === true) {
                    $status = 'rejected';
                }
            }


            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => $data['comments'],
                'status' => $status,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $claims
                )
            ]);
        });
    }

    public function getBatchClaims($batchId)
    {
        return DB::table('batch_claims')
            ->where('batch_id', $batchId)
            ->pluck('claim_id')
            ->toArray();
    }

    public function getApprovedClaims($claims)
    {
        return DB::table('claims')
            ->where('ondc_review_status', ClaimReviewStatus::APPROVED->value)
            ->whereIn('id', $claims)
            ->pluck('id')
            ->toArray();
    }

    public function getRevertOrRejectedClaims($claims)
    {
        return DB::table('claims')
            ->whereIn('ondc_review_status', [ClaimReviewStatus::REVERTED->value, ClaimReviewStatus::REJECTED->value])
            ->whereIn('id', $claims)
            ->get();
    }
}
