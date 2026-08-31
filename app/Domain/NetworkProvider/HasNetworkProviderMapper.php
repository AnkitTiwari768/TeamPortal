<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Traits\HasFileUpload;
use Illuminate\Support\Facades\DB;

trait HasNetworkProviderMapper
{
    protected function mapToModel(array $validatedData): array
    {
        $roleNames = DB::table('roles')
            ->whereIn('id', $validatedData['roles'])
            ->pluck('name')
            ->implode(',');

        /* =======================
             | CONTACT DETAILS
             ======================= */
        $contactDetails = [
            'email' => $validatedData['contact_details_email'],
            'primary_contact_no' => $validatedData['contact_details_primary_contact_no'],
            'whatsapp_no' => $validatedData['contact_details_whatsapp_no'] ?? null,
            'website' => $validatedData['contact_details_website'],
            'app_store_links' => $validatedData['contact_details_app_store_links'] ?? null,
            'social_media_handles' => $validatedData['contact_details_social_media_handles'] ?? null,
        ];

        /* =======================
             | AUTHORISED PERSON
             ======================= */
        $authorizedPersons = [[
            'name' => $validatedData['authorized_person_details_name'],
            'designation' => $validatedData['authorized_person_details_designation'],
            'designation_name' => $this->getRoleName($validatedData['authorized_person_details_designation']),
            'phone' => $validatedData['authorized_person_details_phone'],
            'email' => $validatedData['authorized_person_details_email'],
            'certificate_id' => $validatedData['authorized_person_details_certificate'],
        ]];

        /* =======================
             | CONFIGURATION DETAILS
             ======================= */
        $configurationDetails = [
            'gst_number' => $validatedData['configuration_details_gst_number'],
            'pan' => $validatedData['configuration_details_pan'],
            'cin' => $validatedData['configuration_details_cin'] ?? null,
            'startup_id' => $validatedData['configuration_details_startup_id'] ?? null,
            'fssai_number' => $validatedData['configuration_details_fssai_number'] ?? null,
            'iec_number' => $validatedData['configuration_details_iec_number'] ?? null,
        ];

        /* =======================
             | BANK DETAILS
             ======================= */
        $bankDetails = [
            'bank_name' => $validatedData['bank_details_bank_name'],
            'ifsc_code' => $validatedData['bank_details_ifsc_code'],
            'account_number' => $validatedData['bank_details_account_number'],
        ];

        /* =======================
             | VALUE PROPOSITION
             ======================= */
        $valueProposition = [
            'short_description' => $validatedData['value_proposition_details_short_description'],
            'additional_services' => $validatedData['value_proposition_details_additional_service'] ?? null,
            'language_supported' => $validatedData['value_proposition_details_language_supported'],
            'language_supported_name' => $this->getLanguageName($validatedData['value_proposition_details_language_supported']),
            'team_scheme_landing_page' => $validatedData['value_proposition_details_team_scheme_landing_page'] ?? null,
            'short_video_pitch' => $validatedData['value_proposition_details_short_video_pitch'] ?? null,
            'flyer_id' => $validatedData['value_proposition_details_flyer'] ?? null,
        ];

        /* =======================
             | COMMERCIAL MODEL
             ======================= */
        $commercialModel = [
            'fee_charge' => $validatedData['commercial_model_details_fee_charge'],
            'fee_type' => $validatedData['commercial_model_details_fee_type'],
            'flat_fee' => $validatedData['commercial_model_details_flat_fee'] ?? null,
            'commission_per_transaction' => $validatedData['commercial_model_details_commission_per_transaction'],
            'other_fees' => $validatedData['commercial_model_details_other_fees'] ?? null,
            'special_offers' => $validatedData['commercial_model_details_special_offer'] ?? null,
            'team_scheme_offers' => $validatedData['commercial_model_details_team_scheme_offers'] ?? null,
            'commercial_model_link' => $validatedData['commercial_model_details_commercial_model_link'],
            'subscription_type' => $validatedData['commercial_model_details_subscription'] ?? null,
            'subscription_type_name' => $this->getAttributeValue($validatedData['commercial_model_details_subscription']) ?? null,
        ];

        $roleSelectionData = [];
        $domains = [];
        $subDomains = [];
        $transactionTypes = [];
        $serviceabilities = [];
        if ($validatedData['role_selection_details']) {
            $data = [];
            foreach ($validatedData['role_selection_details'] as $roleSelection) {
                $domains[] = $roleSelection['domain'] ?? [];
                $data['domain'] = $roleSelection['domain'];
                $data['domain_name'] = $this->getDomainName($roleSelection['domain']);

                $transactionTypes[] = $roleSelection['transaction_type'] ?? [];
                $data['transaction_type'] = $roleSelection['transaction_type'];
                $data['transaction_type_name'] = $this->getAttributeValue($roleSelection['transaction_type']);

                $data['role'] = $roleSelection['role'];
                $data['role_name'] = $this->getRoleName($roleSelection['role']);


                $data['status'] = $roleSelection['status'];
                $data['status_name'] = $this->getAttributeValue($roleSelection['status']);

                $subDomains[] = $roleSelection['ondc_domain_mapping'] ?? [];
                $data['ondc_domain_mapping'] = $roleSelection['ondc_domain_mapping'];

                $serviceabilities[] = $roleSelection['serviceability'] ?? [];
                $data['serviceability'] = $roleSelection['serviceability'];
                $data['serviceability_name'] = $this->getServiceabilityName($roleSelection['serviceability']);

                $roleSelectionData[] = $data;
            }
        }

        return [
            'role_names' => $roleNames,
            'contact_details' => $contactDetails,
            'authorized_person_details' => $authorizedPersons,
            'configuration_details' => $configurationDetails,
            'bank_details' => $bankDetails,
            'value_proposition_details' => $valueProposition,
            'commercial_model_details' => $commercialModel,
            'role_selection_details' => $roleSelectionData,
            'domains' => $domains,
            'transactionTypes' => $transactionTypes,
            'subDomains' => $subDomains,
            'serviceabilities' => $serviceabilities,
        ];
    }

    public function mapToSnpModel(
        string $networkProviderId,
        array $validatedData,
        array $payload,
        ?string $snpId = null
    ) {

        $snpUserName = DB::table('team_snp_scheme')->where('user_id', authId())->value('snp_id');
        $subDomainIds = DB::table('sub_domains')->whereIn('ondc_domain_id', $payload['subDomains'])->pluck('id')->toArray();
        $subDomainIds = [...$payload['subDomains'], ...$subDomainIds];

        $authorizedPerson = $payload['authorized_person_details'][0] ?? [];
        $bank             = $payload['bank_details'] ?? [];
        $config           = $payload['configuration_details'] ?? [];
        $valueProp        = $payload['value_proposition_details'] ?? [];

        return [
            /* =======================
         | PRIMARY
         ======================= */
            'id'                  => $snpId ?? uuid(),
            'network_provider_id'  => $networkProviderId,
            'user_id'             => auth()->id(),
            'snp_id'              => $snpUserName ?? null,

            /* =======================
         | ORGANIZATION
         ======================= */
            'organization_id'     => $validatedData['organization_id'] ?? null,
            'organization_name'   => $validatedData['organization_name'] ?? null,
            'brand_name'          => $validatedData['brand_name'] ?? null,

            /* =======================
         | AUTHORIZED PERSON
         ======================= */
            'snp_name'            => $authorizedPerson['name'] ?? null,
            'designation'         => $authorizedPerson['designation_name'] ?? null,

            'authorized_certificate_document'
            => $authorizedPerson['certificate_id'] ?? null,

            'authorized_certificate_document_original_name'
            => isset($authorizedPerson['certificate_id'])
                ? self::originalName($authorizedPerson['certificate_id'])
                : null,

            /* =======================
         | DOMAIN DETAILS (JSON)
         ======================= */
            'domain'              => isset($payload['domains'])
                ? json_encode($payload['domains'])
                : null,

            'sub_domain'          => isset($subDomainIds)
                ? json_encode($subDomainIds)
                : null,

            'transaction_type'    => isset($payload['transactionTypes'])
                ? json_encode($payload['transactionTypes'])
                : null,

            'state_id'            => isset($payload['serviceabilities'])
                ? json_encode($payload['serviceabilities'])
                : null,

            /* =======================
         | BANK DETAILS
         ======================= */
            'bank_name'           => $bank['bank_name'] ?? null,
            'account_no'          => $bank['account_number'] ?? null,
            'ifsc_code'           => $bank['ifsc_code'] ?? null,

            'cancelled_cheque_document'
            => $payload['cancelled_cheque_document'] ?? null,

            'cancelled_cheque_document_original_name'
            => isset($payload['cancelled_cheque_document'])
                ? self::originalName($payload['cancelled_cheque_document'])
                : null,

            /* =======================
         | TAX DETAILS
         ======================= */
            'gst_number'          => $config['gst_number'] ?? null,
            'pan'                 => $config['pan'] ?? null,

            /* =======================
         | COMMERCIAL
         ======================= */
            'commercial_model'    => $payload['commercial'] ?? null,

            'commercial_model_document'
            => $payload['commercial_model_document'] ?? null,

            'commercial_model_document_original_name'
            => isset($payload['commercial_model_document'])
                ? self::originalName($payload['commercial_model_document'])
                : null,

            /* =======================
         | BUSINESS STATUS
         ======================= */
            'live_seller'         => $payload['live_seller'] ?? null,

            'date_of_going_live_on_ondc'
            => !empty($payload['date_of_going_live_on_ondc'])
                ? date('Y-m-d', strtotime($payload['date_of_going_live_on_ondc']))
                : null,

            'no_of_transactions_done'
            => $payload['no_of_transactions_done'] ?? null,

            /* =======================
         | DESCRIPTION
         ======================= */
            'short_description'   => $valueProp['short_description'] ?? null,

            'description_document'
            => $valueProp['flyer_id'] ?? null,

            'description_document_original_name'
            => isset($valueProp['flyer_id'])
                ? self::originalName($valueProp['flyer_id'])
                : null,

            /* =======================
         | FLAGS
         ======================= */
            'agreecheck'          => $payload['agreecheck'] ?? null,
            'status'              => 0,

            /* =======================
         | TIMESTAMPS
         ======================= */
            'created_at'          => now(),
            'updated_at'          => now(),
        ];
    }


    public function getDomainName($id): ?string
    {
        return DB::table('sub_domains')->where('id', $id)->value('name') ?? '';
    }

    public function getAttributeValue($id): ?string
    {
        return DB::table('attribute_values')
            ->where(function ($query) use ($id) {
                return $query
                    ->where('id', $id)
                    ->orWhere('code', $id);
            })
            ->value('attribute_value');
    }

    public function getRoleName($id): ?string
    {
        return DB::table('roles')->where('id', $id)->value('name') ?? '';
    }

    public function getServiceabilityName($id): ?string
    {
        return DB::table('states')->where('id', $id)->value('name') ?? '';
    }

    public function getLanguageName($id): ?string
    {
        return DB::table('language')->where('id', $id)->value('name') ?? '';
    }
}
