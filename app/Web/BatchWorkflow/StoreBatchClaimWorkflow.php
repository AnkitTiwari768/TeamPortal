<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use Carbon\Carbon;
use App\Enums\Status;
use App\Web\ApplicationWorkflow\WorkflowType;
use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Support\Facades\DB;

class StoreBatchClaimWorkflow
{
    use HasUser, HasBatchWorkflow, HasTimeline;

    public function __construct(private BatchClaimWorkflowService $batchWorkflowService) {}

    public function execute(array $data, ClaimReviewStatus $status): void
    {
        DB::transaction(function ()  use ($data, $status) {

            $userId = auth()->id();

            $fromRoleId = $this->fetchRoleId($userId);

            $workflowTypeId = $this->batchWorkflowService->getWorkflowTypeId(WorkflowType::CLAIM_CATALOGUE);

            $updatedClaim = $batchClaims = [];

            if (hasRole('ca')) {
                $updatedClaim = [
                    'ca_review_status' => $status->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => authId()
                ];

                $batchClaims = [
                    'ca_review_status' => $status->value,
                    'ca_review_status_updated_at' => now(),
                    'ca_review_status_updated_by' => authId()
                ];
            } else if (hasRole('ondc-admin')) {
                $updatedClaim = [
                    'ondc_review_status' => $status->value,
                    'ondc_review_status_updated_at' => now(),
                    'ondc_review_status_updated_by' => authId()
                ];

                $batchClaims = [
                    'ondc_review_status' => $status->value,
                    'ondc_review_status_updated_at' => now(),
                    'ondc_review_status_updated_by' => authId()
                ];
            } else if (hasRole('nsic')) {
                $updatedClaim = [
                    'nsic_review_status' => $status->value,
                    'nsic_review_status_updated_at' => now(),
                    'nsic_review_status_updated_by' => authId()
                ];

                $batchClaims = [
                    'nsic_review_status' => $status->value,
                    'nsic_review_status_updated_at' => now(),
                    'nsic_review_status_updated_by' => authId()
                ];
            } else if (hasRole('nsic-finance')) {
                $updatedClaim = [
                    'nsicfinance_review_status' => $status->value,
                    'nsicfinance_review_status_updated_at' => now(),
                    'nsicfinance_review_status_updated_by' => authId()
                ];
                $batchClaims = [
                    'nsicfinance_review_status' => $status->value,
                    'nsicfinance_review_status_updated_at' => now(),
                    'nsicfinance_review_status_updated_by' => authId()
                ];
            }

            $roleAction = $this->isBatchActionTakenByRole($data['batch_id']);
            //dd($roleAction);
            $batch = DB::table('batches')->where('id', $data['batch_id'])->first();

            if ($status === ClaimReviewStatus::APPROVED) {





                if ($batch->is_resend_to_nsic) {
                    DB::table('batch_claims')->where('batch_id', $data['batch_id'])
                        ->where('claim_id', $data['claim_id'])
                        ->update([
                            'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value
                        ]);
                }


                $this->saveNextWorkflow($data, $workflowTypeId, $userId, $fromRoleId, $status);

                DB::table('claims')->where('id', $data['claim_id'])->update($updatedClaim);
                if (hasRole('ca') || hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance')) {
                    DB::table('batch_claims')
                        ->where('batch_id', $data['batch_id'])
                        ->where('claim_id', $data['claim_id'])
                        ->update($batchClaims);
                }



                if (hasRole('nsic') && $batch->is_resend_to_nsic && $roleAction == 1) {

                    $pendingsForNsic = DB::table('batch_claims AS bc')
                        //->join('claims as c', 'bc.claim_id', '=', 'c.id')
                        ->where('bc.batch_id', $data['batch_id'])
                        ->where('bc.nsic_review_status', ClaimReviewStatus::PENDING->value)
                        ->count();



                    if ($pendingsForNsic === 0) {
                        /*DB::table('batches')
								->where('id', $data['batch_id'])
								->update(['is_resend_to_nsic' => null, 'is_resend_to_nsic_finance' => true]);*/
                        DB::table('batches')
                            ->where('id', $data['batch_id'])
                            ->update(['is_resend_to_nsic' => null]);
                        DB::table('claims')
                            ->where('id', $data['claim_id'])
                            ->update([
                                'is_sent_nsicfinance' => true,
                                'nsic_review_status' => ClaimReviewStatus::APPROVED->value,
                                'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value
                            ]);
                    } else { //added by priya
                        /*DB::table('batches')
								->where('id', $data['batch_id'])
								->update(['is_resend_to_nsic_finance' => true]);*/
                        DB::table('claims')
                            ->where('id', $data['claim_id'])
                            ->update([
                                'is_sent_nsicfinance' => true,
                                'nsic_review_status' => ClaimReviewStatus::APPROVED->value,
                                'nsicfinance_review_status' => ClaimReviewStatus::PENDING->value
                            ]);
                    }

                    $roleActionfinance = $this->isBatchActionTakenByFinance($data['batch_id']); {
                        if ($roleActionfinance == 1) {
                            DB::table('batches')
                                ->where('id', $data['batch_id'])
                                ->update(['is_resend_to_nsic_finance' => true]);
                        }
                    }

                    $this->createTimeline([
                        'batch_id' => $data['batch_id'],
                        'comments' => $data['comments'],
                        'status' => 'approved',
                        'claims' => $this->createBatchClaimTimelineDetails(
                            batchId: $data['batch_id'],
                            claimIds: [$data['claim_id']],
                            status: 'approved'
                        )
                    ]);
                }

                if (hasRole('nsic-finance') && $batch->is_resend_to_nsic_finance && $roleAction == 1) {
                    $pendingForNsicFinance = DB::table('batch_claims AS bc')
                        ->join('claims as c', 'bc.claim_id', '=', 'c.id')
                        ->where('bc.batch_id', $data['batch_id'])
                        ->where('c.nsicfinance_review_status', ClaimReviewStatus::PENDING->value)
                        ->count();

                    if ($pendingForNsicFinance === 0) {
                        DB::table('batches')
                            ->where('id', $data['batch_id'])
                            ->update(['is_resend_to_nsic_finance' => null]);
                        DB::table('claims')
                            ->where('id', $data['claim_id'])
                            ->update([
                                'review_status' => ClaimReviewStatus::APPROVED->value,
                                'status' => ClaimReviewStatus::APPROVED->value,
                                'is_sent_nsicfinance' => true,
                                'nsicfinance_review_status' => ClaimReviewStatus::APPROVED->value
                            ]);
                    } else { //added by priya
                        DB::table('claims')
                            ->where('id', $data['claim_id'])
                            ->update([
                                'review_status' => ClaimReviewStatus::APPROVED->value,
                                'status' => ClaimReviewStatus::APPROVED->value,
                                'is_sent_nsicfinance' => true,
                                'nsicfinance_review_status' => ClaimReviewStatus::APPROVED->value
                            ]);
                    }

                    $this->createTimeline([
                        'batch_id' => $data['batch_id'],
                        'comments' => $data['comments'],
                        'status' => 'approved',
                        'claims' => $this->createBatchClaimTimelineDetails(
                            batchId: $data['batch_id'],
                            claimIds: [$data['claim_id']],
                            status: 'approved'
                        )
                    ]);
                }
            }

            if ($status === ClaimReviewStatus::REVERTED || $status === ClaimReviewStatus::REJECTED) {


                if (hasRole('ca') || hasRole('ondc-admin') || hasRole('nsic') || hasRole('nsic-finance')) {
                    $this->revertWorkflowByCA($data, $workflowTypeId, $userId, $fromRoleId, $status);
                }

                if (hasRole('ca') || hasRole('ondc-admin')) {
                    $batchClaims += [
                        'status_id' => $status->value
                    ];
                }


                if (hasRole('nsic')) {

                    if ($status === ClaimReviewStatus::REJECTED) {
                        $batchClaims += [
                            'is_reject_to_snp' => true,
                            'status_id' => $status->value
                        ];

                        //added for batch table update after bacth approved by nsic in case of rejected
                        if ($roleAction == 1) {
                            DB::table('batches')->where('id', $data['batch_id'])->update(['is_rejected_to_snp' => true]);
                        }
                    }
                    //added for batch table update after bacth approved by nsic in case of reverted
                    if ($roleAction == 1) {
                        //DB::table('batch_claims')$data['claim_id']
                        if (isset($data['is_revert_to_ondc']) && $data['is_revert_to_ondc'] == true)
                            DB::table('batches')->where('id', $data['batch_id'])->update(['is_reverted_to_ondc' => true]);
                        if (isset($data['is_revert_to_snp']) && $data['is_revert_to_snp'] == true)
                            DB::table('batches')->where('id', $data['batch_id'])->update(['is_reverted_to_snp' => true]);
                    }

                    if ($batch->is_resend_to_nsic) {
                        $batchClaims += [
                            'is_resend_process_by_nsic' => true,
                        ];
                    }


                    //if resend to nsic and batch review status not equal to approved then bc status_id = 6


                    //$this->isBatchActionTakenByRole($data['batch_id']);

                    $batchClaims += [
                        'is_revert_to_ondc' => $data['is_revert_to_ondc'] ?? null,
                        'is_revert_to_snp' => $data['is_revert_to_snp'] ?? null,
                        'status_id' => $status->value
                    ];
                }

                if (hasRole('nsic-finance')) {
                    $batchClaims += [
                        'is_revert_to_nsic' => $data['is_revert_to_nsic'] ?? null,
                        'status_id' => $status->value
                    ];
                }

                DB::table('batch_claims')
                    ->where('batch_id', $data['batch_id'])
                    ->where('claim_id', $data['claim_id'])
                    ->update($batchClaims);

                if (!hasRole('ca')) {
                    // $updatedClaim += [
                    //     'status' => $status->value,
                    //     'review_status' => $status->value,
                    // ];
                }

                DB::table('claims')->where('id', $data['claim_id'])->update($updatedClaim);
            }
        });
    }


    protected function isBatchActionTakenByRole($batchId)
    {
        return DB::table('batch_review_statuses')->where('batch_id', $batchId)->whereIn('status_id', [ClaimReviewStatus::APPROVED->value])->where('role_id', authRoleId())->count();
    }

    protected function isBatchActionTakenByFinance($batchId)
    {
        return DB::table('batch_review_statuses')->where('batch_id', $batchId)->whereIn('status_id', [ClaimReviewStatus::APPROVED->value])->where('role_id', '9f91336d-d391-42cd-842c-63017bb48de0')->count();
    }
}
