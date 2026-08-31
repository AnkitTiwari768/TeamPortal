<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasClaimNetAmountPayable;

class BatchClaimResource extends JsonResource
{
    use HasClaimNetAmountPayable;
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        if (
            isset($this->is_invoice_reupload_requested)
            && $this->is_invoice_reupload_requested === 1
            && isset($this->claim_status_from_claims)
        ) {
            $claimStatus = BatchStatus::getLabelByValue($this->claim_status_from_claims);
        } else {
            $claimStatus = BatchStatus::getLabelByValue($this->claim_status);
        }

        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id ?? null,
            'snp_name' => $this->snp_name ?? null,
            'application_number' => $this->application_number ?? null,
            'team_id' => $this->team_registration_id ?? null,
            'udyam_no' => $this->msme_udyam_number ?? null,
            'msme_name' => $this->msme_name ?? null,
            'msme_classification' => $this->msme_classification ?? null,
            'major_activity' => $this->major_activity ?? null,
            'target_customer' => $this->target_customer ?? null,
            'seller_provider_id' => $this->seller_provider_id ?? null,
            'provider_id' => $this->provider_id ?? null,
            'status' => $claimStatus,
            'onboarding_date' => $this->onboarding_date ?  date('d-m-Y', strtotime($this->onboarding_date)) : null,
            'is_bulk' => $this->is_bulk ?? null,
            'is_edited' => $this->is_edited ?? null,
            'is_deleted' => $this->is_deleted ?? null,
            'amount' => $this->amount,
            'gst_percentage' => $this->gst_percentage ?? null,
            'gst_amount' => $this->gst_amount ?? null,
            'tds_percentage' => $this->tds_percentage ?? null,
            'tds_amount' => $this->tds_amount ?? null,
            'sgst_percentage' => $this->sgst_percentage ?? null,
            'sgst_amount' => $this->sgst_amount ?? null,
            'cgst_percentage' => $this->cgst_percentage ?? null,
            'cgst_amount' => $this->cgst_amount ?? null,
            'subdomain_names' => $this->subdomain_names ?? null,
            'batch_id' => $this->batch_id ?? null,

            'sgst_tds_percentage' => $this->sgst_tds_percentage ?? null,
            'sgst_tds_amount' => $this->sgst_tds_amount ?? null,
            'cgst_tds_percentage' => $this->cgst_tds_percentage ?? null,
            'cgst_tds_amount' => $this->cgst_tds_amount ?? null,
            'igst_tds_amount' => $this->igst_tds_amount ?? null,

            'total_unique_mse_count' => $this->total_unique_mse_count ?? null,
            'total_cumulative_transaction_count' => $this->total_cumulative_transaction_count ?? null,
            'low_aov_unique_mse_count' => $this->low_aov_unique_mse_count ?? null,
            'low_aov_cumulative_transaction_count' => $this->low_aov_cumulative_transaction_count ?? null,
            'high_aov_unique_mse_count' => $this->high_aov_unique_mse_count ?? null,
            'high_aov_cumulative_transaction_count' => $this->high_aov_cumulative_transaction_count ?? null,
            'total_claimed_amount' => $this->total_claimed_amount,
            'is_query_open' => (bool) ($this->is_query_open ?? false),
        ];
    }
}
