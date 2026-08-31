<?php

declare(strict_types=1);

namespace App\Web\Claim;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Domain\Batch\BatchStatus;
use App\Traits\HasClaimNetAmountPayable;

class BatchClaimQueryListResource extends JsonResource
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
            'status' => BatchStatus::getLabelByValue($this->claim_status),
            'onboarding_date' => $this->onboarding_date ?  date('d-m-Y', strtotime($this->onboarding_date)) : null,
            'is_bulk' => $this->is_bulk ?? null,
            'is_edited' => $this->is_edited ?? null,
            'is_deleted' => $this->is_deleted ?? null,
            'amount' => $this->amount ?? null,
            'gst_percentage' => $this->gst_percentage ?? null,
            'gst_amount' => $this->gst_amount ?? null,
            'tds_percentage' => $this->tds_percentage ?? null,
            'tds_amount' => $this->tds_amount ?? null,
            'subdomain_names' => $this->subdomain_names ?? null,
            'batch_id' => $this->batch_id ?? null,

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
