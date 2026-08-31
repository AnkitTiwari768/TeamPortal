<?php 

declare(strict_types=1);

namespace App\Web\BNP;

use Illuminate\Http\Resources\Json\JsonResource;

class BNPResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'organization_id' => $this->organization_id,
            'bnp_status' => $this->bnp_status,
            'bnp_id' => $this->bnp_id,
            'review_status' => $this->review_status,
            'organization_name' => $this->organization_name,
            'bank_name' => $this->bank_name,
            'ifsc_code' => $this->ifsc_code,
            'account_no' => $this->account_no,
            //'status' => $this->status,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'name' => $this->first_name,
        ];
    }
}