<?php

declare(strict_types=1);

namespace App\Domain\AICataloguingClaim;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasClaimNetAmountPayable;

class AICataloguingClaimListResource extends JsonResource
{
    use HasClaimNetAmountPayable;

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id ?? null,
            'snp_name' => $this->snp_name ?? null,
            'application_number' => $this->application_number ?? null,
            'bpp_id' => $this->bpp_id ?? null,
            'status' => $this->status ? ucfirst(strtolower(ClaimReviewStatus::from((int)$this->status)->name)) : null,
            'is_bulk' => $this->is_bulk ?? null,
            'amount' => $this->amount,
            'gst_percentage' => $this->gst_percentage ?? null,
            'gst_amount' => $this->gst_amount ?? null,
            'sgst_percentage' => $this->sgst_percentage ?? null,
            'sgst_amount' => $this->sgst_amount ?? null,
            'cgst_percentage' => $this->cgst_percentage ?? null,
            'cgst_amount' => $this->cgst_amount ?? null,
            'tds_percentage' => $this->tds_percentage ?? null,
            'tds_amount' => $this->tds_amount ?? null,
            'sgst_tds_percentage' => $this->sgst_tds_percentage ?? null,
            'sgst_tds_amount' => $this->sgst_tds_amount ?? null,
            'cgst_tds_percentage' => $this->cgst_tds_percentage ?? null,
            'cgst_tds_amount' => $this->cgst_tds_amount ?? null,
            'total_claimed_amount' => $this->total_claimed_amount,
            
            // AI Cataloguing specific columns
            'team_registration_id' => $this->team_registration_id ?? null,
            'msme_name' => $this->msme_name ?? null,
            'msme_udyam_number' => $this->msme_udyam_number ?? null,
            'catalogue_id' => $this->catalogue_id ?? null,
            'catalogue_finalization_date' => $this->catalogue_finalization_date ?? null,
            'catalogue_completion_status' => $this->catalogue_completion_status ?? null,
            'digital_catalogue_footprint' => $this->digital_catalogue_footprint ?? null,
            'amount_claimed_with_financial_reconciliation' => $this->amount_claimed_with_financial_reconciliation ?? null,
        ];
    }
}
