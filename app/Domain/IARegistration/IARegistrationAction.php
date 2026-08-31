<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IARegistrationAction
{
    public function execute(array $data): IndustrialAssociation
    {
        return DB::transaction(function () use ($data) {


            $registration = IndustrialAssociation::create([
                'id' => (string) Str::uuid(),
                'user_id' => null,
                'organization_name' => $data['organization_name'],
                'entity_type' => $data['entity_type'],
                'entity_email' => $data['entity_email'],
                'number_of_members' => $data['number_of_members'] ?? null,
                'registration_number' => $data['registration_number'] ?? null,
                'website' => $data['website'] ?? null,
                'contact_number' => $data['contact_number'],
                'pan_number' => $data['pan_number'] ?? null,
                'state_id' => $data['state_id'],
                'district_id' => $data['district_id'],
                'complete_address' => $data['complete_address'],
                'contact_person_name' => $data['contact_person_name'],
                'contact_person_designation' => $data['contact_person_designation'],
                'contact_person_phone' => $data['contact_person_phone'],
                'contact_person_email' => $data['contact_person_email'],
                'authorization_document' => $data['authorization_document'] ?? null,
                'authorization_document_id' => $data['authorization_document_id'] ?? null,
                'status' => IAStatus::PENDING->value,
            ]);

            return $registration;
        });
    }
}
