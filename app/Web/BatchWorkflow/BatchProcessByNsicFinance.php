<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\ApplicationWorkflow\WorkflowType;
use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

class BatchProcessByNsicFinance
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

            $revertedToNsicClaims = $approvedClaims = [];

            foreach ($batchClaims as $row) {
                if ($row->is_revert_to_nsic) {
                    $revertedToNsicClaims[] = $row->id;
                }
            }

            $approvedClaims = DB::table('claims')
                ->where('nsicfinance_review_status', ClaimReviewStatus::APPROVED->value)
                ->pluck('id')
                ->toArray();

            if ($revertedToNsicClaims) {
                DB::table('batches')
                    ->where('id', $batchId)
                    ->update(['is_reverted_to_nsic' => true]);

                DB::table('batch_claims')
                    ->whereIn('id', $revertedToNsicClaims)
                    ->update(['status_id' => ClaimReviewStatus::REVERTED->value, 'is_revert_to_nsic' => true, 'updated_at' => now(), 'updated_by' => authId()]);
            }

            $status = '';

            if ($approvedClaims) {
                DB::table('batches')->where('id', $batchId)->update([
                    'status_id' => ClaimReviewStatus::APPROVED->value,
                    'updated_at' => now(),
                    'updated_by' => authId()
                ]);

                DB::table('claims')->whereIn('id', $approvedClaims)->update([
                    'status' => ClaimReviewStatus::APPROVED->value,
                    'review_status' => ClaimReviewStatus::APPROVED->value,
                    'nsicfinance_review_status' => ClaimReviewStatus::APPROVED->value,
                    'nsicfinance_review_status_updated_at' => now(),
                    'nsicfinance_review_status_updated_by' => authId()
                ]);

                DB::table('batch_claims')
                    ->where('batch_id', $batchId)
                    ->whereIn('claim_id', $approvedClaims)
                    ->update([
                        'status_id' => ClaimReviewStatus::APPROVED->value,
                    ]);

                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', authRoleId())
                    ->update(['status_id' => ClaimReviewStatus::APPROVED->value]);

                $status = 'approved';
            }

            if (!empty($revertedToNsicClaims) && empty($approvedClaims)) {

                DB::table('batches')->where('id', $batchId)->update([
                    'updated_at' => now(),
                    'updated_by' => authId(),
                    'status_id' => ClaimReviewStatus::REVERTED->value
                ]);

                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', $authRoleId)
                    ->update(['status_id' => ClaimReviewStatus::REVERTED->value]);

                $status = 'reverted';
            }

            //To update batch is_resend_to_nsic_finance false in case of batch process
            DB::table('batches')
                ->where('id', $batchId)
                ->update(['is_resend_to_nsic_finance' => null]);

            // dd($batchClaims);

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
