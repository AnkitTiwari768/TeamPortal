<?php

declare(strict_types=1);

namespace App\Web\BatchWorkflow;

use App\Web\Claim\ClaimReviewStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait HasBatchWorkflow
{
    public function getBatchReviewAction($batchId)
    {
        if (hasRole('ca')) {
            return $this->getBatchClaimActionForCA($batchId);
        }

        if (hasRole('snp')) {
            return $this->getBatchClaimActionForSnp($batchId);
        }

        if (hasRole('ondc-admin')) {
            return $this->getBatchClaimActionForOndc($batchId);
        }

        if (hasRole('nsic')) {
            return $this->getBatchClaimActionForNsic($batchId);
        }

        /*if (hasRole('nsic-finance')) {
            return $this->getBatchClaimActionForNsicFinance($batchId);
        }*/

        if (hasRole('nsic-finance')) {
            $financeActions = $this->getBatchClaimActionForNsicFinance($batchId);

            // Only show payment if final approval is done
            if (! $financeActions['showFinalApproval']) {
                $payment = $this->getActionForPaymentCompleted($batchId);
            } else {
                $payment = [];
            }

            return array_merge($financeActions, $payment);
        }
    }

    protected function getBatchClaimActionForCA($batchId)
    {
        $claimsReviewed = DB::table('batch_claim_workflow')
            ->where('batch_id', $batchId)
            ->where('from_user_id', authId())
            ->pluck('claim_id')
            ->toArray();

        $areAllClaimsReviewed = false;

        $claims = DB::table('batch_claims as bc')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId);

        $claimStatuses = $claims->pluck('c.ca_review_status');
        $claims = $claims->get();
        $allApproved = $claimStatuses->every(fn($status) => ((int) $status) === ClaimReviewStatus::APPROVED->value);
        $allReverted = $claimStatuses->every(fn($status) => ((int) $status) === ClaimReviewStatus::REVERTED->value);
        $anyReverted = null;
        $anyApproved = null;
        /*if (! $allApproved) {
            $anyReverted = in_array(ClaimReviewStatus::REVERTED->value, $claimStatuses->toArray());
        }*/

        if (! $allReverted) {
            $anyApproved = in_array(ClaimReviewStatus::APPROVED->value, $claimStatuses->toArray());
        }

        foreach ($claims as $claim) {
            if (!in_array($claim->id, $claimsReviewed)) {
                $areAllClaimsReviewed = false;
                break;
            } else {
                $areAllClaimsReviewed = true;
            }
        }

        /*return [
            'showApprove' => ($areAllClaimsReviewed && $allApproved) ? true : false,
            'showRevert' => ($areAllClaimsReviewed && $anyReverted) ? true : false
        ];*/

        return [
            'showApprove' => ($areAllClaimsReviewed && $anyApproved) ? true : false,
            'showRevert' => ($areAllClaimsReviewed && $allReverted) ? true : false
        ];
    }

    protected function getBatchClaimActionForSnp($batchId)
    {
        $filterReviewStatus = request()->input('filters');


        $revertedClaims = DB::table('batch_claims')
            ->where('batch_id', $batchId)
            ->where('status_id', ClaimReviewStatus::REVERTED->value)
            ->whereNull('is_deleted')
            ->where('is_revert_to_snp', 0) //added by priya
            ->pluck('claim_id')
            ->toArray();

        $showSentToCA = false;
        if ($revertedClaims) {
            $claims = DB::table('claims')->whereIn('id', $revertedClaims)->get();
            foreach ($claims as $claim) {
                if ($claim->is_edited !== 1) {
                    $showSentToCA = false;
                    break;
                } else {
                    $showSentToCA = true;
                }
            }
        }

        $batch = DB::table('batches')->where('id', $batchId)->first();

        $showMoveToDraft = false;

        if ($batch->is_reverted_by_ondc || $batch->is_rejected_by_ondc) {
            $showMoveToDraft = true;
        }

        if (isset($filterReviewStatus['review_status']) && $filterReviewStatus['review_status'] === 'Certified By CA') {
            $showMoveToDraft = false;
        }

        return [
            'show_sent_to_ca' => $showSentToCA,
            'show_move_to_draft' => $showMoveToDraft
        ];
    }

    protected function getBatchClaimActionForOndc($batchId)
    {
        $isSentToNsic = DB::table('batches')->where('id', $batchId)->value('is_sent_nsic');

        if ($isSentToNsic) {
            return [
                'show_send_to_nsic' => false
            ];
        }

        $claims = DB::table('batch_claims')->where('batch_id', $batchId)->whereNull('is_deleted')->pluck('claim_id')->toArray();

        $claimsReviewed = DB::table('batch_claim_workflow')
            ->where('batch_id', $batchId)
            ->where('from_user_id', authId())
            ->pluck('claim_id')
            ->toArray();

        $showSendToNsic = false;

        if ($claims && $claimsReviewed) {
            foreach ($claims as $claimId) {
                if (! in_array($claimId, $claimsReviewed)) {
                    $showSendToNsic = false;
                    break;
                } else {
                    $showSendToNsic = true;
                }
            }
        }

        return [
            'show_send_to_nsic' => $showSendToNsic
        ];
    }

    protected function getBatchClaimActionForNsic($batchId)
    {
        $isSentToNsicFinance = DB::table('batches')->where('id', $batchId)->value('is_sent_nsic_finance');

        $claims = DB::table('batch_claims as bc')
            ->select('c.nsic_review_status')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->whereNull('bc.is_deleted')
            ->get();

        $areAllClaimsReviewed = false;

        if ($claims) {
            foreach ($claims as $claim) {
                if ($claim->nsic_review_status === ClaimReviewStatus::PENDING->value || $claim->nsic_review_status === null) {
                    $areAllClaimsReviewed = false;
                    break;
                } else {
                    $areAllClaimsReviewed = true;
                }
            }
        }

        return [
            'showForwardToNsicFinance' => (!$isSentToNsicFinance && $areAllClaimsReviewed) ? true : false,
        ];
    }

    protected function getBatchClaimActionForNsicFinance($batchId)
    {
        $reviewStatus = (int) DB::table('batches')->where('id', $batchId)->value('status_id');

        $claims = DB::table('batch_claims as bc')
            ->select('c.nsicfinance_review_status')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->where('c.is_sent_nsicfinance', true)
            ->whereNull('bc.is_deleted')
            ->get();

        $areAllClaimsReviewed = false;

        if ($claims) {
            foreach ($claims as $claim) {
                if ($claim->nsicfinance_review_status === ClaimReviewStatus::PENDING->value || $claim->nsicfinance_review_status === null) {
                    $areAllClaimsReviewed = false;
                    break;
                } else {
                    $areAllClaimsReviewed = true;
                }
            }
        }

        return [
            'showFinalApproval' => ($reviewStatus != ClaimReviewStatus::APPROVED->value && $areAllClaimsReviewed) ? true : false,
        ];
    }

    protected function saveNextWorkflow(mixed ...$configs)
    {
        [$data, $workflowTypeId, $userId, $fromRoleId, $status] = $configs;

        $nextWorkflow = $this->batchWorkflowService->getNextBatchWorkflow($userId, $workflowTypeId);

        $batchClaimWorkflow = [];

        foreach ($nextWorkflow as $workflow) {
            $batchClaimWorkflow[] = [
                'id' => uuid(),
                'batch_id' => $data['batch_id'],
                'claim_id' => $data['claim_id'],
                'from_role_id' => $fromRoleId,
                'from_user_id' => $userId,
                'to_role_id' => $workflow->role_id,
                'comments' => $data['comments'] ?? null,
                'status_id' => $status->value,
                'created_at' => Carbon::now(),
                'created_by' => $userId
            ];
        }

        if (!$batchClaimWorkflow) {
            $batchClaimWorkflow[] = [
                'id' => uuid(),
                'batch_id' => $data['batch_id'],
                'claim_id' => $data['claim_id'],
                'from_role_id' => $fromRoleId,
                'from_user_id' => $userId,
                'to_role_id' => $fromRoleId,
                'comments' => $data['comments'] ?? null,
                'status_id' => $status->value,
                'created_at' => Carbon::now(),
                'created_by' => $userId
            ];
        }


        if ($batchClaimWorkflow) {
            DB::table('batch_claim_workflow')->insert($batchClaimWorkflow);
        }
    }


    protected function revertWorkflowByCA($data, $workflowTypeId, $userId, $fromRoleId, $status): void
    {
        $revertToUserId = $this->batchWorkflowService->getBatchCreatedUserId($data['batch_id']);

        if (hasRole('nsic-finance') || hasRole('nsic')) {
            $revertToUserId = null;
        }

        $batchClaimWorkflow = [
            'id' => uuid(),
            'batch_id' => $data['batch_id'],
            'claim_id' => $data['claim_id'],
            'from_role_id' => $fromRoleId,
            'from_user_id' => $userId,
            'to_role_id' => $this->fetchRoleIdBySlug('snp'),
            'to_user_id' => $revertToUserId,
            'comments' => $data['comments'] ?? null,
            'status_id' => $status->value,
            'created_at' => Carbon::now(),
            'created_by' => $userId
        ];

        DB::table('batch_claim_workflow')->insert($batchClaimWorkflow);
    }

    protected function getActionForPaymentCompleted($batchId)
    {
        $reviewStatus = (int) DB::table('batches')->where('id', $batchId)->value('status_id');

        $claims = DB::table('batch_claims as bc')
            ->select('c.status')
            ->join('claims as c', 'bc.claim_id', '=', 'c.id')
            ->where('bc.batch_id', $batchId)
            ->where('c.is_sent_nsicfinance', true)
            ->whereNull('bc.is_deleted')
            ->get();

        $areAllClaimsApproved = false;

        if ($claims) {
            foreach ($claims as $claim) {
                if ($claim->status != ClaimReviewStatus::APPROVED->value) {
                    $areAllClaimsApproved = false;
                    break;
                } else {
                    $areAllClaimsApproved = true;
                }
            }
        }

        return [
            'showPaymentButton' => ($areAllClaimsApproved) ? true : false,
        ];
    }
}
