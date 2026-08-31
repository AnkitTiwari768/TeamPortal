<?php

declare(strict_types=1);

namespace App\Web\Batch;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Web\Claim\ClaimReviewStatus;
use App\Web\BatchWorkflow\HasBatchWorkflow;

class BatchResource extends JsonResource
{
    use HasBatchWorkflow;

    protected $extraData;


    public function toArray($request)
    {
        // dump($this);
        return [
            'id' => $this->id,
            'batch_number' => $this->batch_number,
            'financial_year' => $this->financial_year,
            'month' => $this->month,
            'month_name' => $this->month ? date('F', mktime(0, 0, 0, $this->month, 1)) : null,
            'description' => $this->description,
            'is_ca_certified' => $this->is_ca_certified,
            'is_sent_ca' => $this->is_sent_ca,
            'is_sent_ondc' => $this->is_sent_ondc,
            'is_sent_nsic' => $this->is_sent_nsic,
            'is_sent_nsic_finance' => $this->is_sent_nsic_finance,
            'status_id' => ucfirst(strtolower(ClaimReviewStatus::from($this->status_id)->name)),
            'status' => $this->status_id,
            'batchStatus' => ucfirst(strtolower(ClaimReviewStatus::from($this->batchStatus)->name)) ?? null,
            'created_at' => $this->created_at ?  date('d-m-Y', strtotime($this->created_at)) : null,
            'total_claims' => $this->total_number_of_claims??null, //$this->getTotalClaimsCount() ?? null,
            'claim_amount' => $this->total_amount??null, //$this->getTotalClaimsAmount() ?? null,
            'snp_name'   => $this->snp_name,
            'snp_id'   => $this->snp_id,
            'mark_payment_completed' => $this->mark_payment_completed??null,
            'show_move_to_draft_in_rejected' => $this->show_move_to_draft_in_rejected??null,
            'show_sent_to_ondc' => $this->show_sent_to_ondc??null,
            'batch_button' => $this->getBatchReviewAction($this->id) ?? null,

        ];
    }

    private function getTotalClaimsCount()
    {
        $reviewStatus = null;

        $filters = request()->input('filters');

        if (isset($filters['review_status'])) {
            $reviewStatus = ClaimReviewStatus::getIdByName($filters['review_status']);
        }

        if ($reviewStatus === ClaimReviewStatus::REVERTED->value) {
            if (hasRole('snp')) {
                if ((isset($this->is_reverted_by_ondc) && $this->is_reverted_by_ondc) || (isset($this->is_reverted_to_snp) && $this->is_reverted_to_snp) || (isset($this->is_reverted_to_snp) && $this->is_reverted_to_snp)) return $this->total_claims_by_ondc + $this->total_claims_by_nsic;
            }
            if (hasRole('ondc-admin')) {
                if (isset($this->is_reverted_to_ondc) && $this->is_reverted_to_ondc) return $this->total_claims_of_ondc;
            }
        }

        if ($reviewStatus === ClaimReviewStatus::REJECTED->value) {
            if (isset($this->is_rejected_by_ondc) && $this->is_rejected_by_ondc) return $this->total_rej_claims_by_ondc;
        }

        /*if ($reviewStatus === ClaimReviewStatus::PENDING->value) {
            if (isset($this->is_reverted_to_ondc) && $this->is_reverted_to_ondc) return $this->total_claims_reverted_to_ondc;
        }*/

        return $this->total_claims;
    }

    private function getTotalClaimsAmount()
    {
        $reviewStatus = null;

        $filters = request()->input('filters');

        if (isset($filters['review_status'])) {
            $reviewStatus = ClaimReviewStatus::getIdByName($filters['review_status']);
        }

        if ($reviewStatus === ClaimReviewStatus::REVERTED->value) {
            if (hasRole('snp')) {
                if ((isset($this->is_reverted_by_ondc) && $this->is_reverted_by_ondc) || (isset($this->is_reverted_to_snp) && $this->is_reverted_to_snp)) return $this->total_amount_rev_by_ondc + $this->total_amount_rev_by_nsic;
            }
            if (hasRole('ondc-admin')) {
                if (isset($this->is_reverted_to_ondc) && $this->is_reverted_to_ondc) return $this->total_amount_rev_of_ondc;
            }
        }

        if ($reviewStatus === ClaimReviewStatus::REJECTED->value) {
            if (isset($this->is_rejected_by_ondc) && $this->is_rejected_by_ondc) return $this->total_amount_rej_by_ondc;
        }

        return $this->total_amount;
    }
}
