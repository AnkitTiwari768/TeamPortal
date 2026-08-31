<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Services\PasswordGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApproveIA
{
    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {

            $industrialAssocition = IndustrialAssociation::where('id', $data['ia_registration_id'])->first();

            $generatedPassword = PasswordGenerator::generate();
            $hashedPassword = Hash::make($generatedPassword);
            $userId = uuid();

            DB::table('users')->insert([
                'id' => $userId,
                'first_name' => $industrialAssocition->organization_name,
                'username' => $industrialAssocition->entity_email,
                'password' => $hashedPassword,
                'email' => $industrialAssocition->entity_email,
                'mobile' => $industrialAssocition->contact_number,
                'status' => true,
                'created_at' => now(),
                'created_by' => authId()
            ]);

            $roleId = DB::table('roles')
                ->where('slug', 'ia-registration')
                ->value('id');

            DB::table('user_roles')->insert([
                'user_id' => $userId,
                'role_id' => $roleId,
            ]);

            IndustrialAssociation::where('id', $data['ia_registration_id'])->update([
                'user_id' => $userId,
                'status' => IAStatus::APPROVE->value,
                'status_updated_at' => now(),
                'review_remarks' => $data['remarks'] ?? null,
            ]);

            // send credentials  to mail    
            app(EmailTemplateService::class)->send(
                templateKey: 'ia-registration-credentials',
                toEmail: $industrialAssocition->entity_email,
                data: [
                    'name' => $industrialAssocition->contact_person_name,
                    'username' => $industrialAssocition->entity_email,
                    'password' => $generatedPassword,
                    'text' => 'for registering'
                ]
            );
        });
    }
}
