<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Domain\EmailTemplate\EmailTemplateService;
use App\Enums\EntityType;
use App\Services\PasswordGenerator;
use App\Web\Timeline\HasTimeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ApproveNetworkProviderAction
{
    use HasGenerateNetworkProviderUsername, HasNetworkProviderMapper, HasTimeline;


    public function execute(array $validatedData)
    {
      
        DB::transaction(function () use ($validatedData) {

            $networkProvider = $this->getNetworkProviderById($validatedData['network_provider_id']);
            $networkProviderUserId = $networkProvider->user_id;

            if ($networkProvider->is_new_registration && empty($networkProviderUserId)) {
                
                // New Registration
                $generatedUsername = $networkProvider->np_team_id;
                $generatedPassword = PasswordGenerator::generate();
                $userId            = uuid();

                // Create New User
                DB::table('users')->insert([
                    'id'            => $userId,
                    'username'      => $generatedUsername,
                    'password'      => Hash::make($generatedPassword),
                    'first_name'    => $networkProvider->organization_name,
                    'email'         => $networkProvider->email,
                    'mobile'        => data_get($networkProvider->contact_details, 'primary_contact_no'),
                    'status'        => true,
                    'updated_at'    => now(),
                    'updated_by'    => authId()
                ]);

                // Map User to Role(s)
                $userRoles = [];
                $roles = json_decode($networkProvider->roles);
                if (is_array($roles)) {
                    foreach ($roles as $roleId) {
                        $userRoles[] = [
                            'user_id' => $userId,
                            'role_id' => $roleId
                        ];
                    }
                } else {
                    $userRoles = [
                        'user_id' => $userId,
                        'role_id' => $roles
                    ];
                }

                DB::table('user_roles')->insert($userRoles);

                // Update NP TEAM ID
                NetworkProvider::where('id', $validatedData['network_provider_id'])->update([
                    'user_id' => $userId,
                    'np_team_id' => $generatedUsername,
                    'status' => NetworkProviderStatus::APPROVE->value,
                    'status_updated_at' => now(),
                    'review_remarks' => $validatedData['remarks'] ?? null,
                ]);

                $attachments = [];
                
                if ($networkProvider->is_snp) {
                    // Create entry in Team SNP Table
                    DB::table('team_snp_scheme')
                        ->where('network_provider_id', $networkProvider->id)
                        ->update([
                            'user_id' => $userId,
                            'status' => NetworkProviderStatus::APPROVE->value,
                            'snp_id' => $networkProvider->np_team_id
                    ]);

                    if ($filePath = $this->generateSnpEmpanelmentLetter($networkProvider)) {
                        $attachments[] = [
                            'path' => $filePath,
                            'name' => 'SNP_Empanelment_Letter.pdf'
                        ];
                    }
                }

                // Add Timeline
                $authUserName = auth()->user()->first_name;
                $this->addTimeline([
                    'entity_id' => $networkProvider->id,
                    'entity_type' => EntityType::NETWORK_PROVIDER->value,
                    'subject' => "The registration request of NP - {$networkProvider->organization_name} has been approved by {$authUserName}",
                    'comment' => $validatedData['remarks'] ?? null,
                    'status' => 'Approved'
                ]);

                // Send Credentials to User's Email
                app(EmailTemplateService::class)->send(
                    templateKey: 'np-registration-approved',
                    toEmail: $networkProvider->email,
                    data: [
                        'email' => config('settings.helpdesk_email'), //$user->username,
                        'username' => $generatedUsername,
                        'password' => $generatedPassword,
                        'year' => (string) date('Y')
                    ],
                    attachments: $attachments
                );
            } elseif ($networkProvider->is_new_registration && !empty($networkProviderUserId)) {
                
                // Update NP TEAM ID
                NetworkProvider::where('id', $validatedData['network_provider_id'])->update([
                    'status' => NetworkProviderStatus::APPROVE->value,
                    'status_updated_at' => now(),
                    'review_remarks' => $validatedData['remarks'] ?? null,
                ]);

                // Map User to Role(s)
                $userRoles = [];
                $roles = json_decode($networkProvider->roles);
                if (is_array($roles)) {
                    foreach ($roles as $roleId) {
                        $userRoles[] = [
                            'user_id' => $networkProviderUserId,
                            'role_id' => $roleId
                        ];
                    }
                } else {
                    $userRoles = [
                        'user_id' => $networkProviderUserId,
                        'role_id' => $roles
                    ];
                }

                DB::table('user_roles')->where('user_id', $networkProviderUserId)->delete();
                DB::table('user_roles')->insert($userRoles);

                // Add Timeline
                $authUserName = auth()->user()->first_name;
                $this->addTimeline([
                    'entity_id' => $networkProvider->id,
                    'entity_type' => EntityType::NETWORK_PROVIDER->value,
                    'subject' => "The registration request of NP - {$networkProvider->organization_name} has been approved by {$authUserName}",
                    'comment' => $validatedData['remarks'] ?? null,
                    'status' => 'Approved'
                ]);

                $attachments = [];

                if ($networkProvider->is_snp) {
                    if ($filePath = $this->generateSnpEmpanelmentLetter($networkProvider)) {
                        $attachments[] = [
                            'path' => $filePath,
                            'name' => 'SNP_Empanelment_Letter.pdf'
                        ];
                    }
                }
                // Send Credentials to User's Email
                app(EmailTemplateService::class)->send(
                    templateKey: 'np-registration-approved',
                    toEmail: $networkProvider->email,
                    data: [
                        'email' => config('settings.helpdesk_email'),
                        'username' => $networkProvider->np_team_id,
                        'password' => '<No change in your password - use your existing password>',
                        'year' => (string) date('Y')
                    ],
                    attachments: $attachments
                );
            } elseif (! $networkProvider->is_new_registration) {
                

                // Map User to Role(s)
                $snpUserId = DB::table('team_snp_scheme')->where('snp_id', $networkProvider->np_team_id)->value('user_id');

                // Update NP TEAM ID
                NetworkProvider::where('id', $validatedData['network_provider_id'])->update([
                    'status' => NetworkProviderStatus::APPROVE->value,
                    'user_id' => $snpUserId,
                    'status_updated_at' => now(),
                    'review_remarks' => $validatedData['remarks'] ?? null,
                ]);

                 // Update status in team_snp_scheme using network_provider_id
                    DB::table('team_snp_scheme')
                        ->where('network_provider_id', $networkProvider->id)
                        ->update([
                            'status' => NetworkProviderStatus::APPROVE->value
                        ]);

                $userRoles = [];
                $roles = json_decode($networkProvider->roles);
                if (is_array($roles)) {
                    foreach ($roles as $roleId) {
                        $userRoles[] = [
                            'user_id' => $snpUserId,
                            'role_id' => $roleId
                        ];
                    }
                } else {
                    $userRoles = [
                        'user_id' => $snpUserId,
                        'role_id' => $roles
                    ];
                }

                DB::table('user_roles')->where('user_id', $snpUserId)->delete();
                DB::table('user_roles')->insert($userRoles);

                // Add Timeline
                $authUserName = auth()->user()->first_name;
                $this->addTimeline([
                    'entity_id' => $networkProvider->id,
                    'entity_type' => EntityType::NETWORK_PROVIDER->value,
                    'subject' => "The registration request of NP - {$networkProvider->organization_name} has been approved by {$authUserName}",
                    'comment' => $validatedData['remarks'] ?? null,
                    'status' => 'Approved'
                ]);

                $attachments = [];

                if ($networkProvider->is_snp) {
                    if ($filePath = $this->generateSnpEmpanelmentLetter($networkProvider)) {
                        $attachments[] = [
                            'path' => $filePath,
                            'name' => 'SNP_Empanelment_Letter.pdf'
                        ];
                    }
                }

                // Send Credentials to User's Email
                app(EmailTemplateService::class)->send(
                    templateKey: 'np-registration-approved',
                    toEmail: $networkProvider->email,
                    data: [
                        'email' => config('settings.helpdesk_email'),
                        'username' => $networkProvider->np_team_id,
                        'password' => '<No change in your password - use your existing password>',
                        'year' => (string) date('Y')
                    ],
                    attachments: $attachments
                );
            }
        });
    }

    public function getNetworkProviderById(string $networkProviderId)
    {
        return NetworkProvider::where('id', $networkProviderId)->first();
    }


    // public function execute(array $validatedData)
    // {
    //     DB::transaction(function () use ($validatedData) {

    //         $networkProvider = NetworkProvider::where('id', $validatedData['network_provider_id'])->first();

    //         $userId = $this->getNetworkProviderUserId($validatedData['network_provider_id']);

    //         $snpUserId = DB::table('network_providers')->where('id', $networkProvider->id)->value('np_team_id');

    //         if ($userId || $snpUserId) {

    //             if ($userId) {
    //                 DB::table('users')->where('id', $userId)->update([
    //                     'first_name' => $networkProvider->organization_name,
    //                     'email' => $networkProvider->email,
    //                     'mobile' => data_get($networkProvider->contact_details, 'primary_contact_no'),
    //                     'status' => true,
    //                     'updated_at' => now(),
    //                     'updated_by' => authId()
    //                 ]);
    //             }

    //             if ($snpUserId) {
    //                 $generatedUsername = $snpUserId;
    //                 $userId = DB::table('users')->where('username', $snpUserId)->value('id');
    //             } else {
    //                 $generatedUsername = DB::table('users')->where('id', $userId)->value('username');
    //             }

    //             if ($userId) {
    //                 DB::table('team_snp_scheme')->where('snp_id', $generatedUsername)->update([
    //                     'user_id' => $userId,
    //                     'network_provider_id' => $networkProvider->id
    //                 ]);
    //             }
    //         } else {

    //             $generatedPassword = PasswordGenerator::generate();
    //             $hashedPassword = Hash::make($generatedPassword);
    //             $userId = uuid();

    //             $generatedUsername = DB::table('network_providers')->where('id', $validatedData['network_provider_id'])->value('np_team_id');

    //             DB::table('users')->insert([
    //                 'id' => $userId,
    //                 'first_name' => $networkProvider->organization_name,
    //                 'username' => $generatedUsername,
    //                 'password' => $hashedPassword,
    //                 'email' => $networkProvider->email,
    //                 'mobile' => data_get($networkProvider->contact_details, 'primary_contact_no'),
    //                 'status' => true,
    //                 'created_at' => now(),
    //                 'created_by' => authId()
    //             ]);
    //         }

    //         if (!$userId) {
    //             $generatedPassword = PasswordGenerator::generate();
    //             $hashedPassword = Hash::make($generatedPassword);
    //             $userId = uuid();

    //             $generatedUsername = DB::table('network_providers')->where('id', $validatedData['network_provider_id'])->value('np_team_id');

    //             DB::table('users')->insert([
    //                 'id' => $userId,
    //                 'first_name' => $networkProvider->organization_name,
    //                 'username' => $generatedUsername,
    //                 'password' => $hashedPassword,
    //                 'email' => $networkProvider->email,
    //                 'mobile' => data_get($networkProvider->contact_details, 'primary_contact_no'),
    //                 'status' => true,
    //                 'created_at' => now(),
    //                 'created_by' => authId()
    //             ]);
    //         }

    //         $userRoles = [];
    //         $networkProvider->roles = json_decode($networkProvider->roles, true);

    //         $roleSlugs = DB::table('roles')->whereIn('id', $networkProvider->roles)->pluck('slug')->toArray();

    //         if (in_array('snp', $roleSlugs)) {
    //             DB::table('team_snp_scheme')->where('network_provider_id', $networkProvider->id)->update(['user_id' => $userId]);
    //         }

    //         foreach ($networkProvider->roles as $roleId) {
    //             $userRoles[] = [
    //                 'user_id' => $userId,
    //                 'role_id' => $roleId
    //             ];
    //         }

    //         DB::table('user_roles')->where('user_id', $userId)->delete();
    //         DB::table('user_roles')->insert($userRoles);



    //         NetworkProvider::where('id', $validatedData['network_provider_id'])->update([
    //             'user_id' => $userId,
    //             'np_team_id' => $generatedUsername,
    //             'status' => NetworkProviderStatus::APPROVE->value,
    //             'status_updated_at' => now(),
    //             'review_remarks' => $validatedData['remarks'] ?? null,
    //         ]);



    //         $authUserName = auth()->user()->first_name;

    //         $this->addTimeline([
    //             'entity_id' => $networkProvider->id,
    //             'entity_type' => EntityType::NETWORK_PROVIDER->value,
    //             'subject' => "The registration request of NP - {$networkProvider->organization_name} has been approved by {$authUserName}",
    //             'comment' => $validatedData['remarks'] ?? null,
    //             'status' => 'Approved'
    //         ]);

    //         app(EmailTemplateService::class)->send(
    //             templateKey: 'np-registration-approved',
    //             toEmail: $networkProvider->email,
    //             data: [
    //                 'name' => $networkProvider->organization_name,
    //                 'username' => $generatedUsername,
    //                 'password' => $generatedPassword ?? '<No change in your password - use your existing password>',
    //                 'text' => 'for registering'
    //             ]
    //         );
    //     });
    // }

    public function getNetworkProviderUserId(string $networkProviderId)
    {
        return NetworkProvider::where('id', $networkProviderId)->value('user_id');
    }


    protected function generateSnpEmpanelmentLetter($networkProvider): ?string {
        try {

            $logo1Path = base_path('/assets/img/snp_certificate_logo_1.png');
            $logo2Path = base_path('/assets/img/snp_certificate_logo_2.png');

            $data = [
                'organization_name' => $networkProvider->organization_name,
                'current_date'      => now()->format('d-M-Y'),
                'logo1' => base64_encode(file_get_contents($logo1Path)),
                'logo2' => base64_encode(file_get_contents($logo2Path)),
            ];

            $pdf = Pdf::loadView('pdf.snp_empanelment_letter', $data);

            $fileName = 'SNP_' . $networkProvider->np_team_id . '.pdf';
            $relativePath = 'temp_snp_letters/' . $fileName;

            Storage::disk('local')->put($relativePath, $pdf->output());

            return storage_path('app/' . $relativePath);

        } catch (\Throwable $e) {

                Log::error('PDF generation failed: '.$e->getMessage());

                return null;
            }
    }    

}
