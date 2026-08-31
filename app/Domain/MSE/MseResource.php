<?php

declare(strict_types=1);

namespace App\Domain\MSE;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class MseResource extends JsonResource
{
    public function toArray($request)
    {
        $createdAt = Carbon::parse($this->created_at);
        $expiryDate = $createdAt->addDays(15);

        return [
            'id' => $this->id,
            'udyam_no' => $this->udyam_no ?? null,
            'mobile' => $this->mobile,
            'email' => $this->email,
            'entrepreneur_name' => $this->entrepreneur_name,
            'enterprise_name' => $this->enterprise_name,
            'organisation_type' => $this->organisation_type,
            'msme_classification' => $this->msme_classification,
            'major_activity' => $this->major_activity ?? null,
            'social_category' => $this->social_category,
            'state_name' => $this->state_name ?? null,
            'bonus_amount' => $this->bonus_amount ?? null,
            'team_id' => $this->team_id ?? null,
            'is_msme_registration' => $this->is_msme_registration ?? null,
            'select_snp' => $this->select_snp ?? null,
            'created_at' => date('d-m-Y', strtotime($this->created_at)),
            'days_left' => Carbon::now()->diffInDays($expiryDate, false),
            'registration_source' => $this->registration_source ?? null,
            'source_of_registration' => isset($this->is_user_sso) && $this->is_user_sso == 1 ? 'UBP' : ($this->association_name ?? $this->creator_snp_name ?? 'Self'),
            'transaction_type' => $this->transaction_type ?? null,
            'bpp_id' => $this->bpp_id ?? null,
        ];
    }
}
