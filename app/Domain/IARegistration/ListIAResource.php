<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use Illuminate\Http\Resources\Json\JsonResource;

class ListIAResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,

            'organization_name' => $this->organization_name,
            'entity_type' => $this->entity_type,
            'entity_email' => $this->entity_email,
            'number_of_members' => $this->number_of_members,
            'registration_number' => $this->registration_number,
            'website' => $this->website,
            'contact_number' => $this->contact_number,
            'pan_number' => $this->pan_number,

            'state_id' => $this->state_id,
            'state_name' => $this?->state_name ?? null,
            'district_id' => $this->district_id,
            'district_name' => $this?->district_name ?? null,
            'complete_address' => $this->complete_address,

            'contact_person' => [
                'name' => $this->contact_person_name,
                'designation' => $this->contact_person_designation,
                'phone' => $this->contact_person_phone,
                'email' => $this->contact_person_email,
            ],

            'authorization_document' => $this->authorization_document_path . '/' .  $this->authorization_document,

            'status' => (int) $this->status,
            'statusWithLabel' => $this->getStatusLabel($this->status),
            'review_remarks' => $this->review_remarks ?? null,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }

    private function getStatusLabel(int $status): string
    {
        return match ($status) {
            IAStatus::PENDING->value => '<span class="badge bg-warning">Pending</span>',
            IAStatus::APPROVE->value => '<span class="badge bg-success">Approved</span>',
            IAStatus::REJECT->value => '<span class="badge bg-reject">Rejected</span>',
            default => 'N/A'
        };
    }
}
