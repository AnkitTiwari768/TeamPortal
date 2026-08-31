<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

use App\Domain\Language\Language;
use Illuminate\Support\Facades\DB;

class GetNetworkProviderDetailsAction
{
    public function execute(string $id)
    {
        $networkProvider = NetworkProvider::findOrFail($id);

        if ($networkProvider->contact_details) {
            $networkProvider->contact_details = json_decode($networkProvider->contact_details, true);
            $networkProvider->email = data_get($networkProvider->contact_details, 'email');
            $networkProvider->website = data_get($networkProvider->contact_details, 'website');
            $networkProvider->whatsapp_no = data_get($networkProvider->contact_details, 'whatsapp_no');
            $networkProvider->app_store_links = data_get($networkProvider->contact_details, 'app_store_links');
            $networkProvider->primary_contact_no = data_get($networkProvider->contact_details, 'primary_contact_no');
            $networkProvider->social_media_handles = data_get($networkProvider->contact_details, 'social_media_handles');
        }

        if ($networkProvider->authorized_person_details) {
            $networkProvider->authorized_person_details = json_decode($networkProvider->authorized_person_details, true);
            $networkProvider->authorized_person_name = data_get($networkProvider->authorized_person_details, '0.name');
            $networkProvider->authorized_person_designation = $this->getRoleNameById(data_get($networkProvider->authorized_person_details, '0.designation'));
            $networkProvider->authorized_person_email = data_get($networkProvider->authorized_person_details, '0.email');
            $networkProvider->authorized_person_contact_no = data_get($networkProvider->authorized_person_details, '0.phone');

            $document = $this->getFileDetails(data_get($networkProvider->authorized_person_details, '0.certificate_id'));

            if ($document) {
                $networkProvider->authorized_person_certificate = $document['document_link'];
                $networkProvider->authorized_person_certificate_name = $document['document_name'];
            }
        }

        if ($networkProvider->role_selection_details) {
            $networkProvider->role_selection_details = json_decode($networkProvider->role_selection_details, true);
        }

        if ($networkProvider->configuration_details) {
            $networkProvider->configuration_details = json_decode($networkProvider->configuration_details, true);
            $networkProvider->cin = data_get($networkProvider->configuration_details, 'cin');
            $networkProvider->pan = data_get($networkProvider->configuration_details, 'pan');
            $networkProvider->gst_number = data_get($networkProvider->configuration_details, 'gst_number');
            $networkProvider->iec_number = data_get($networkProvider->configuration_details, 'iec_number');
            $networkProvider->startup_id = data_get($networkProvider->configuration_details, 'startup_id');
            $networkProvider->fssai_number = data_get($networkProvider->configuration_details, 'fssai_number');
        }

        if ($networkProvider->bank_details) {
            $networkProvider->bank_details = json_decode($networkProvider->bank_details, true);
            $networkProvider->bank_name = data_get($networkProvider->bank_details, 'bank_name');
            $networkProvider->ifsc_code = data_get($networkProvider->bank_details, 'ifsc_code');
            $networkProvider->account_number = data_get($networkProvider->bank_details, 'account_number');
        }

        if ($networkProvider->value_proposition_details) {
            $networkProvider->value_proposition_details = json_decode($networkProvider->value_proposition_details, true);
            $networkProvider->flyer = data_get($networkProvider->value_proposition_details, 'flyer');
            $networkProvider->short_description = data_get($networkProvider->value_proposition_details, 'short_description');
            $networkProvider->short_video_pitch = data_get($networkProvider->value_proposition_details, 'short_video_pitch');

            $languageIds = (array) data_get($networkProvider->value_proposition_details, 'language_supported', []);
            $networkProvider->language_supported = Language::whereIn('id', $languageIds)->pluck('name')->implode(', ');

            $networkProvider->additional_services = data_get($networkProvider->value_proposition_details, 'additional_services');


            $document = $this->getFileDetails(data_get($networkProvider->value_proposition_details, 'flyer_id'));

            if ($document) {
                $networkProvider->flyer_document = $document['document_link'];
                $networkProvider->flyer_document_name = $document['document_name'];
            }

            $networkProvider->team_scheme_landing_page = data_get($networkProvider->value_proposition_details, 'team_scheme_landing_page');
        }

        if ($networkProvider->commercial_model_details) {
            $networkProvider->commercial_model_details = json_decode($networkProvider->commercial_model_details, true);

            $feeTypeValue = data_get($networkProvider->commercial_model_details, 'fee_type');
            $networkProvider->fee_type = $feeTypeValue ? FeeType::tryFrom($feeTypeValue)?->label() : '';

            $networkProvider->flat_fee = data_get($networkProvider->commercial_model_details, 'flat_fee');
            $networkProvider->fee_charge = data_get($networkProvider->commercial_model_details, 'fee_charge');
            $networkProvider->other_fees = data_get($networkProvider->commercial_model_details, 'other_fees');
            $networkProvider->special_offers = data_get($networkProvider->commercial_model_details, 'special_offers');
            $networkProvider->team_scheme_offers = data_get($networkProvider->commercial_model_details, 'team_scheme_offers');
            $networkProvider->commercial_model_link = data_get($networkProvider->commercial_model_details, 'commercial_model_link');
            $networkProvider->commission_per_transaction = data_get($networkProvider->commercial_model_details, 'commission_per_transaction');
        }

        return $networkProvider;
    }

    public function getRoleNameById(string $roleId): ?string
    {
        return DB::table('roles')->where('id', $roleId)->value('name');
    }

    public function getFileDetails(string $fileId)
    {
        $file = DB::table('file_uploads')->where('id', $fileId)->first();

        if ($file) {
            return [
                'document_link' => url('storage/app/' . $file->file_path . '/' . $file->file_system_name),
                'document_name' => $file->file_name
            ];
        }

        return [];
    }
}
