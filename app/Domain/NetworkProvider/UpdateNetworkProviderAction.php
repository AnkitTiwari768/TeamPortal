<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Traits\HasFileUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UpdateNetworkProviderAction
{
    use HasFileUpload, HasNetworkProviderMapper;

    public function execute(array $validatedData, bool $isDraft = false): bool
    {
     
        return DB::transaction(function () use ($validatedData, $isDraft) {


            /* =======================
             | PERSIST PROVIDER
             ======================= */
            $networkProviderId = $validatedData['id'];

            if ($this->profileRequestIsPending($networkProviderId)) {
                return false;
            }

            $payload = $this->mapToModel($validatedData);

            $snpRoleName = DB::table('roles')->where('slug', 'snp')->value('name');
            $isSNP = false;
            $roleNames = explode(',', $payload['role_names']);

            if (in_array($snpRoleName, $roleNames)) {
                $isSNP = true;
            }

            $contactDetails = $payload['contact_details'];
            $authorizedPersons = $payload['authorized_person_details'];
            $configurationDetails = $payload['configuration_details'];
            $bankDetails = $payload['bank_details'];
            $valueProposition = $payload['value_proposition_details'];
            $commercialModel = $payload['commercial_model_details'];
            $roleSelectionData = $payload['role_selection_details'];


            $provider = NetworkProvider::findOrFail($networkProviderId);

            $provider->fill([
                'user_id' => auth()->id(),
                'roles' => json_encode($validatedData['roles']),
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
                'is_snp' => $isSNP,
                'status_updated_at' => Carbon::now()->timestamp,
                'review_remarks' => null,
                'updated_by' => auth()->id(),
            ]);

            $provider->save();

            if (!$provider->is_new_registration && $provider->is_snp) {
                
                $snpId = DB::table('team_snp_scheme')->where('network_provider_id', $networkProviderId)->value('id');

                $snpData = $this->mapToSnpModel($networkProviderId, $validatedData, $payload, $snpId);

                $snpData['network_provider_id'] = $networkProviderId;

                DB::table('team_snp_scheme')->where('id', $snpId)->update($snpData);

                DB::table('network_providers')->where('id', $networkProviderId)->update(['np_team_id' => $snpData['snp_id']]);
            }

            return true;
        });
    }

    private function profileRequestIsPending(string $networkProviderId)
    {
        $status = (int) DB::table('network_providers')->where('id', $networkProviderId)->value('status');

        return $status === NetworkProviderStatus::PENDING->value;
    }
}
