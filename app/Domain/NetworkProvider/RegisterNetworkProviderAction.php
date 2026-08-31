<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Enums\EntityType;
use App\Traits\HasFileUpload;
use App\Web\Timeline\HasTimeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class RegisterNetworkProviderAction
{
    use HasFileUpload, HasNetworkProviderMapper, HasTimeline, HasGenerateNetworkProviderUsername;

    public function execute(array $validatedData, bool $isDraft = false): NetworkProvider|bool
    {
        return DB::transaction(function () use ($validatedData, $isDraft) {

            /* =======================
             | PERSIST PROVIDER
             ======================= */
            $networkProviderId = uuid();

            $payload = $this->mapToModel($validatedData);

            $snpRoleName = DB::table('roles')->where('slug', 'snp')->value('name');
            $isSNP = false;
            $roleNames = explode(',', $payload['role_names']);



            if (in_array($snpRoleName, $roleNames)) {
                $isSNP = true;
            }

            $authId = authId();
            $snpUserName = $authId ? DB::table('team_snp_scheme')->where('user_id', $authId)->value('snp_id') : null;


            if ($snpUserName) {
                $isNew = false;
            } else {
                $isNew = true;
            }


            if ($snpUserName && $this->profileRequestIsPending($snpUserName)) {
                return false;
            }


            $contactDetails = $payload['contact_details'];
            $authorizedPersons = $payload['authorized_person_details'];
            $configurationDetails = $payload['configuration_details'];
            $bankDetails = $payload['bank_details'];
            $valueProposition = $payload['value_proposition_details'];
            $commercialModel = $payload['commercial_model_details'];
            $roleSelectionData = $payload['role_selection_details'];

            $generatedUsername = $snpUserName ? $snpUserName : $this->generateUsername();

            $networkProvider = NetworkProvider::create([
                'id' => $networkProviderId,
                'np_team_id' => $generatedUsername,
                'roles' => json_encode($validatedData['roles']),
                'role_names' => $payload['role_names'],
                'organization_id' => $validatedData['organization_id'],
                'organization_name' => $validatedData['organization_name'],
                'email' => $validatedData['email'],
                'bppid_providerid' => $validatedData['bppid_providerid'],

                'contact_details' => json_encode($contactDetails),
                'authorized_person_details' => json_encode($authorizedPersons),
                'configuration_details' => json_encode($configurationDetails),
                'bank_details' => json_encode($bankDetails),
                'role_selection_details' => json_encode($roleSelectionData),
                'value_proposition_details' => json_encode($valueProposition),
                'commercial_model_details' => json_encode($commercialModel),

                'status' => $isDraft ? 0 : 1,
                'status_updated_at' => Carbon::now()->timestamp,
                'review_remarks' => null,
                'is_new_registration' => $isNew,
                'is_snp' => $isSNP,
                'created_by' => $networkProviderId,
                'updated_by' => null,
            ]);

            if ($isNew) {
                $snpData = $this->mapToSnpModel(
                    $networkProviderId,
                    $validatedData,
                    $payload
                );
                // dd(1, $snpData);
                DB::table('team_snp_scheme')->insert($snpData);
            } else {
                $snpId = DB::table('team_snp_scheme')->where('network_provider_id', $networkProviderId)->value('id');

                $snpData = $this->mapToSnpModel($networkProviderId, $validatedData, $payload, $snpId);

                $snpData['network_provider_id'] = $networkProviderId;
                $snpData['is_profile_updated'] = true;

                // dd(2, $snpData);
                DB::table('team_snp_scheme')->where('id', $snpId)->update($snpData);

                DB::table('network_providers')->where('id', $networkProviderId)->update(['np_team_id' => $snpData['snp_id']]);
            }


            // if (str_contains($payload['role_names'], 'SNP')) {

            //     $snpData = $this->mapToSnpModel($networkProviderId, $validatedData, $payload);

            //     if ($snpData['snp_id']) {
            //         DB::table('network_providers')->where('id', $networkProviderId)->update([
            //             'np_team_id' => $snpData['snp_id']
            //         ]);
            //     } else {
            //         $snpData['snp_id'] = $generatedUsername;
            //         DB::table('team_snp_scheme')->insert($snpData);
            //     }
            // }

            $this->addTimeline([
                'entity_id' => $networkProvider->id,
                'entity_type' => EntityType::NETWORK_PROVIDER->value,
                'subject' => "The registration request has been submitted by NP - {$networkProvider->organization_name}",
                'comment' => $validatedData['remarks'] ?? null,
                'status' => 'Submitted'
            ]);

            return $networkProvider;
        });
    }

    private function profileRequestIsPending(string $snpUserName)
    {
        $status = (int) DB::table('network_providers')->where('np_team_id', $snpUserName)->value('status');

        return $status === NetworkProviderStatus::PENDING->value;
    }
}
