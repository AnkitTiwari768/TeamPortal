<?php

declare(strict_types=1);

namespace App\Web\SNP;

use Illuminate\Http\Resources\Json\JsonResource;

class MigratedSnpListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'snp_id' => $this->snp_id,
            'organization_id' => $this->organization_id,
            'organization_name' => $this->organization_name,
            'email' => $this->email,
            //'role_names' => $this->designation,
            'role_name' => $this->role_name,
            'email' => $this->email,
            'authorized_person_name' => $this->first_name,
            'primary_contact_no' => $this->mobile,
            'status' => $this->snp_status,
            'created_at' => optional($this->created_at)->format('d-m-Y H:i:s'),
        ];
    }
}
