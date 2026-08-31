<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

class ProcessByNsicToFinance
{
    use HasTimeline;

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $batchId = $data['batch_id'];
            $claimId = $data['claim_id'];

            $pendingsForNsic = DB::table('batch_claims AS bc')
                ->where('bc.batch_id', $data['batch_id'])
                ->where('bc.is_revert_to_nsic', true)
                ->where('bc.claim_id', '<>', $claimId)
                ->count();


            DB::table('claims')->where('id', $claimId)->update([
                'status' => ClaimReviewStatus::PENDING->value,
                'review_status' => ClaimReviewStatus::PENDING->value,
                'is_edited' => 0,
                'nsic_review_status' => ClaimReviewStatus::APPROVED->value,
                'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value
            ]);

            DB::table('batch_claims')
                ->where('batch_id', $batchId)
                ->where('claim_id', $claimId)
                ->update([
                    'status_id' => ClaimReviewStatus::PENDING->value,
                    'is_revert_to_nsic' => false,
                    'nsic_review_status' => ClaimReviewStatus::APPROVED->value,
                    'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value
                ]);


            if ($pendingsForNsic === 0) {
                DB::table('batches')->where('id', $batchId)->update([
                    'is_reverted_to_nsic' => null
                ]);
            }

            DB::table('batches')->where('id', $batchId)->update([
                'is_resend_to_nsic_finance' => true
            ]);

            $roleAction = $this->isBatchActionTakenByNSICFinanceRole($batchId);
            if ($roleAction == 0) {
                DB::table('batch_review_statuses')
                    ->where('batch_id', $batchId)
                    ->where('role_id', '9f91336d-d391-42cd-842c-63017bb48de0') // nsic finance role id
                    ->update(['status_id' => ClaimReviewStatus::PENDING->value]);
            }

            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => $data['comments'],
                'status' => 'resend',
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: [$claimId],
                    status: 'resend'
                )
            ]);
        });
    }

    public function isBatchActionTakenByNSICFinanceRole($batchId)
    {
        return DB::table('batch_review_statuses')
            ->where('batch_id', $batchId)
            ->whereIn('status_id', [ClaimReviewStatus::APPROVED->value])
            ->where('role_id', '9f91336d-d391-42cd-842c-63017bb48de0')  //nsic role id
            ->count();
    }
}
