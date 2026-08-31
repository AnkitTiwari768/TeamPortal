<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\ApplicationWorkflow\WorkflowType;
use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

class BatchProcessByNsic
{
    use HasUser, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $authId = auth()->id();

            $fromRoleId = $authRoleId = $this->fetchRoleId($authId);

            $batchId = $data['batch_id'];

            $batchClaims = DB::table('batch_claims')->where('batch_id', $batchId)->get();

            $revertedToOndcClaims = $revertedToSnpClaims = $rejectedClaims = $approvedClaims = [];

            $status = '';

            foreach ($batchClaims as $row) {
                if ($row->is_revert_to_ondc) {
                    $revertedToOndcClaims[] = $row->id;
                }

                if ($row->is_revert_to_snp) {
                    //$revertedToSnpClaims[] = $row->id; //commented by priya
                    $revertedToSnpClaims[] = $row->claim_id; //added by priya
                }

                if ($row->status_id === ClaimReviewStatus::REJECTED->value && $row->is_deleted === null) {
                    //$rejectedClaims[] = $row->id;//commented by priya
                    $rejectedClaims[] = $row->claim_id; //added by priya
                }
            }

            $approvedClaims = DB::table('claims')
                ->where('nsic_review_status', ClaimReviewStatus::APPROVED->value)
                ->pluck('id')
                ->toArray();

            if ($revertedToOndcClaims) {
                DB::table('batches')
                    ->where('id', $batchId)
                    ->update(['is_reverted_to_ondc' => true]);

                DB::table('batch_claims')
                    ->whereIn('id', $revertedToOndcClaims)
                    ->update(['status_id' => ClaimReviewStatus::REVERTED->value, 'updated_at' => now(), 'updated_by' => authId(), 'is_resend_process_by_nsic' => null]);
            }

            if ($revertedToSnpClaims) {
                DB::table('batches')
                    ->where('id', $batchId)
                    ->update(['is_reverted_to_snp' => true]);

                DB::table('batch_claims')
                    //->whereIn('id', $revertedToSnpClaims) //commented by priya
                    ->whereIn('claim_id', $revertedToSnpClaims) //added by priya
                    ->whereNull('is_deleted') //added by priya
                    ->update(['status_id' => ClaimReviewStatus::REVERTED->value, 'updated_at' => now(), 'updated_by' => authId(), 'is_resend_process_by_nsic' => null]);

                DB::table('claims')->whereIn('id', $revertedToSnpClaims)->update([
                    'status' => ClaimReviewStatus::REVERTED->value,
                    'review_status' => ClaimReviewStatus::REVERTED->value,
                ]);
            }
            // dd($rejectedClaims, $revertedToOndcClaims, $revertedToSnpClaims);
            if ($rejectedClaims) {

                DB::table('batch_claim_histories')
                    ->where('batch_id', $batchId)
                    ->whereIn('claim_id', $rejectedClaims)
                    ->update([
                        'status_id' => ClaimReviewStatus::REJECTED->value,
                        'updated_at' => now(),
                        'updated_by' => $authId
                    ]);

                DB::table('batch_claims')
                    ->where('batch_id', $batchId)
                    ->whereIn('claim_id', $rejectedClaims)
                    ->whereNull('is_deleted') //added by priya
                    ->update(['is_deleted' => true, 'deleted_by' => authId()]);

                DB::table('claims')->whereIn('id', $rejectedClaims)->update([
                    'status' => ClaimReviewStatus::REJECTED->value,
                    'review_status' => ClaimReviewStatus::REJECTED->value,
                ]);

                DB::table('batches')->where('id', $batchId)->update(['is_rejected_to_snp' => true]);
            }

            if ($approvedClaims) {
                DB::table('batches')->where('id', $batchId)->update([
                    'is_sent_nsic_finance' => true,
                    'updated_at' => now(),
                    'updated_by' => authId()
                ]);

                $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);
                $nextWorkflow = $this->batchWorkflowService->getNextBatchWorkflow($authId, $workflowTypeId);
                $batchWorkflow = [];
                $insertBatchReviewStatus = [];

                foreach ($nextWorkflow as $workflow) {
                    $batchWorkflow[] = [
                        'id' => uuid(),
                        'batch_id' => $batchId,
                        'workflow_id' => $workflow->id,
                        'workflow_level' => $workflow->level,
                        'from_role_id' => $fromRoleId,
                        'from_user_id' => $authId,
                        'to_role_id' => $workflow->role_id,
                        'to_user_id' => $workflow->user_id ?? null,
                        'comments' => $data['comments'] ?? null,
                        'status_id' => ClaimReviewStatus::APPROVED->value,
                        'created_at' => now(),
                        'created_by' => $authId
                    ];

                    $insertBatchReviewStatus[] = [
                        'id' => uuid(),
                        'batch_id' => $batchId,
                        'role_id' => $workflow->role_id,
                        'user_id' => $workflow->user_id ?? null,
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
                    'is_sent_nsicfinance' => true,
                    'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value,
                    'nsicfinance_review_status_updated_at' => now(),
                    'nsicfinance_review_status_updated_by' => authId()
                ]);

                DB::table('batch_claims')->where('batch_id', $batchId)->whereIn('claim_id', $approvedClaims)->update([
                    'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value,
                    'nsicfinance_review_status_updated_at' => now(),
                    'nsicfinance_review_status_updated_by' => authId(),
                    'is_resend_process_by_nsic' => null
                ]);

                $status = 'approved';
            }


            if ((!empty($revertedToOndcClaims) || !empty($revertedToSnpClaims)) && empty($approvedClaims)) {
                /*DB::table('batches')->where('id', $batchId)->update([
                    'updated_at' => now(),
                    'updated_by' => authId(),
					'status_id' => ClaimReviewStatus::REVERTED->value
                ]);*/

                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', $authRoleId)
                    ->update(['status_id' => ClaimReviewStatus::REVERTED->value]);

                $status = 'reverted';
            }


            if (empty($revertedToOndcClaims) && empty($revertedToSnpClaims) && !empty($rejectedClaims)) {

                /*DB::table('batches')->where('id', $batchId)->update([
                    'updated_at' => now(),
                    'updated_by' => authId(),
					'status_id' => ClaimReviewStatus::REJECTED->value
                ]);*/


                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', $authRoleId)
                    ->update(['status_id' => ClaimReviewStatus::REJECTED->value]);

                if (empty($approvedClaims)) {
                    $status = 'rejected';
                }
            }

            //To update batch is_resend_to_nisc false in case of batch process
            DB::table('batches')
                ->where('id', $batchId)
                ->update(['is_resend_to_nsic' => null]);

            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => $data['comments'],
                'status' => $status,
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $batchClaims->pluck('claim_id')->toArray()
                )
            ]);
        });
    }
}
