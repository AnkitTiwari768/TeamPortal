<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Services\PasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RejectIA
{
    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $industrialAssocition = IndustrialAssociation::where('id', $data['ia_registration_id'])->first();

            IndustrialAssociation::where('id', $data['ia_registration_id'])->update([
                'status' => IAStatus::REJECT->value,
                'status_updated_at' => now(),
                'review_remarks' => $data['remarks'] ?? null,
            ]);

            // send credentials  to mail    
            app(EmailTemplateService::class)->send(
                templateKey: 'ia-registration-rejected',
                toEmail: $industrialAssocition->entity_email,
                data: [
                    'name' => $industrialAssocition->contact_person_name,
                    'remarks' => $data['remarks'] ?? null,
                    'text' => 'for registering'
                ]
            );
        });
    }
}
