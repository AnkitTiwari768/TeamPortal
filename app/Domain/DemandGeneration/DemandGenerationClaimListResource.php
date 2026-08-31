<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Traits\HasClaimNetAmountPayable;

class DemandGenerationClaimListResource extends JsonResource
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
            'status' => $this->status ? ucfirst(strtolower(ClaimReviewStatus::from($this->status)->name)) : null,
            'is_bulk' => $this->is_bulk ?? null,
            'amount' => $this->amount - ((float)$this->gst_amount + (float)$this->cgst_amount + (float)$this->sgst_amount),
            'low_aov_eligible_records' => $this->low_aov_eligible_records ?? null,
            'high_aov_eligible_records' => $this->high_aov_eligible_records ?? null,
            'low_aov_unique_mse_count' => $this->low_aov_unique_mse_count ?? null,
            'high_aov_unique_mse_count' => $this->high_aov_unique_mse_count ?? null,
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

            'total_claimed_amount' => $this->calculateNetAmountPayable(
                base: (float) $this->amount, 
                gst: (float) $this->gst_amount, 
                tds: (float) $this->tds_amount, 
                sgst: (float) $this->sgst_amount, 
                cgst:(float)  $this->cgst_amount,
                tds_cgst: (float) $this->cgst_tds_amount,
                tds_sgst: (float) $this->sgst_tds_amount
            ),
        ];
    }

}
