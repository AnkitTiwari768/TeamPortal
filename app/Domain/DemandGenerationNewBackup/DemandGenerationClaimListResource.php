<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use App\Web\Claim\ClaimReviewStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class DemandGenerationClaimListResource extends JsonResource
{
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
            'amount' => $this->amount ?? null,
            'low_aov_eligible_records' => $this->low_aov_eligible_records ?? null,
            'high_aov_eligible_records' => $this->high_aov_eligible_records ?? null,
            'low_aov_unique_mse_count' => $this->low_aov_unique_mse_count ?? null,
            'high_aov_unique_mse_count' => $this->high_aov_unique_mse_count ?? null,
        ];
    }
}
