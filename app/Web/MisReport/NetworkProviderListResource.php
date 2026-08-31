<?php

declare(strict_types=1);

namespace App\Web\MisReport;

use Illuminate\Http\Resources\Json\JsonResource;

class NetworkProviderListResource extends JsonResource
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
            'np_team_id' => $this->np_team_id,
            'organization_id' => $this->organization_id,
            'organization_name' => $this->organization_name,
            'email' => $this->email,
            'bppid_providerid' => $this->bppid_providerid,
            'role_names' => $this->role_names,

            'authorized_person_name' => $this->authorized_person_name
                ?? data_get($this->authorized_person_details, '0.name'),

            'primary_contact_no' => $this->primary_contact_no
                ?? data_get($this->contact_details, 'primary_contact_no'),

            'status' => $this->status,

            'created_at' => \Carbon\Carbon::parse($this->created_at)->format('d-m-Y'),
        ];
    }
}
