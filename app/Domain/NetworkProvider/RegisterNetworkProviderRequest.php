<?php

namespace App\Domain\NetworkProvider;

use Illuminate\Foundation\Http\FormRequest;

class RegisterNetworkProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /**
         * NOTE: All fields are nullable to allow partial updates.
         * Validation will be handled in the service layer based on the
         * current registration step.
         */
        return [

            /* =======================
     | BASIC DETAILS
     ======================= */
            'roles' => ['nullable', 'array', 'min:1'],
            'roles.*' => ['nullable', 'string', 'exists:roles,id'],

            'organization_id' => ['nullable', 'string', 'max:255'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'bppid_providerid' => ['nullable', 'string', 'max:255'],

            'contact_details_email' => ['nullable', 'email:rfc,dns'],
            'contact_details_primary_contact_no' => ['nullable', 'digits_between:8,15'],
            'contact_details_whatsapp_no' => ['nullable', 'digits_between:8,15'],
            'contact_details_website' => ['nullable', 'url', $this->strictUrlRule()],
            'contact_details_app_store_links' => ['nullable', 'string', 'max:255', $this->strictUrlRule()],
            'contact_details_social_media_handles' => ['nullable', 'string', 'max:255', $this->strictUrlRule()],

            /* =======================
     | AUTHORISED PERSON
     ======================= */
            'authorized_person_details_name' => ['nullable', 'string', 'max:255'],
            'authorized_person_details_designation' => ['nullable', 'string', 'max:100'],
            'authorized_person_details_phone' => ['nullable', 'digits_between:8,15'],
            'authorized_person_details_email' => ['nullable', 'email:rfc,dns'],
            'authorized_person_details_certificate' => [
                'nullable',
                'exists:file_uploads,id'
            ],

            /* =======================
     | CONFIGURATION DETAILS
     ======================= */
            'configuration_details_gst_number' => ['nullable', 'string', 'max:20'],
            'configuration_details_pan' => ['nullable', 'string', 'max:10'],
            'configuration_details_cin' => ['nullable', 'string', 'max:21'],
            'configuration_details_startup_id' => ['nullable', 'string'],
            'configuration_details_fssai_number' => ['nullable', 'string'],
            'configuration_details_iec_number' => ['nullable', 'string'],

            /* =======================
     | BANK DETAILS
     ======================= */
            'bank_details_bank_name' => ['nullable', 'string', 'max:255'],
            'bank_details_ifsc_code' => ['nullable', 'string', 'max:11'],
            'bank_details_account_number' => ['nullable', 'digits_between:9,18'],

            /* =======================
     | ROLE SELECTION (ONDC)
     ======================= */
            'role_selection_details' => ['nullable', 'array'],
            'role_selection_details.*.domain' => ['nullable', 'exists:sub_domains,id', 'min:1'],
            'role_selection_details.*.transaction_type' => ['nullable', 'exists:attribute_values,id', 'min:1'],
            'role_selection_details.*.role' => ['nullable', 'exists:roles,id'],
            'role_selection_details.*.status' => ['nullable', 'exists:attribute_values,id'],
            'role_selection_details.*.ondc_domain_mapping' => ['nullable', 'exists:sub_domains,ondc_domain_id'],
            'role_selection_details.*.serviceability' => ['nullable', 'exists:states,id'],

            /* =======================
     | VALUE PROPOSITION
     ======================= */
            'value_proposition_details_short_description' => ['nullable', 'string', 'max:1000'],
            'value_proposition_details_additional_service' => ['nullable', 'string'],
            'value_proposition_details_language_supported' => ['nullable', 'exists:language,id'],
            'value_proposition_details_team_scheme_landing_page' => ['nullable', 'url', $this->strictUrlRule()],
            'value_proposition_details_short_video_pitch' => [
                'nullable',
                'string',
                'url',
                $this->strictUrlRule()
            ],
            'value_proposition_details_flyer' => [
                'nullable',
                'exists:file_uploads,id'
            ],

            /* =======================
     | COMMERCIAL MODEL
     ======================= */
            'commercial_model_details_fee_charge' => ['nullable', 'in:yes,no'],
            'commercial_model_details_fee_type' => ['nullable', 'in:flat_fee,subscription'],
            'commercial_model_details_flat_fee' => ['nullable'],
            'commercial_model_details_subscription' => ['nullable', 'exists:attribute_values,code'],
            'commercial_model_details_commission_per_transaction' => ['nullable'],
            'commercial_model_details_other_fees' => ['nullable', 'string'],
            'commercial_model_details_special_offer' => ['nullable', 'string'],
            'commercial_model_details_team_scheme_offers' => ['nullable', 'string'],
            'commercial_model_details_commercial_model_link' => ['nullable', 'url'],

            /* =======================
     | DECLARATION
     ======================= */
            'declaration' => ['nullable'],
        ];
    }

    public function strictUrlRule()
    {
        return function ($attribute, $value, $fail) {
            if (!filter_var($value, FILTER_VALIDATE_URL)) {
                $fail('The URL must contain a valid path.');
            }
        };
    }

    public function messages(): array
    {
        return [

            /* Basic */
            'roles.required' => 'Please select at least one role.',
            'organization_id.required' => 'Organization ID (as per ONDC) is required.',
            'organization_name.required' => 'Organization legal name is required.',
            'email.required' => 'Official email ID is required.',
            'email.email' => 'Please enter a valid email address.',

            /* Contact */
            'contact_details.primary_contact_no.required' => 'Primary contact number is required.',
            'contact_details.website.required' => 'Official website is required.',
            'contact_details.website.url' => 'Website URL is invalid.',

            /* Authorized Person */
            'authorized_person_details.required' => 'At least one authorised person is required.',
            'authorized_person_details.*.name.required' => 'Authorised person name is required.',
            'authorized_person_details.*.designation.required' => 'Role/Designation is required.',
            'authorized_person_details.*.phone.required' => 'Phone/WhatsApp number is required.',
            'authorized_person_details.*.email.required' => 'Email address is required.',
            'authorized_person_details.*.certificate.required' => 'Authorized certificate is required.',
            'authorized_person_details.*.certificate.mimes' => 'Certificate must be jpg, jpeg, png or pdf.',
            'authorized_person_details.*.certificate.max' => 'Certificate size must not exceed 5MB.',

            /* Configuration */
            'configuration_details.gst_number.required' => 'GST number is required.',
            'configuration_details.pan.required' => 'PAN is required.',

            /* Bank */
            'bank_details.bank_name.required' => 'Bank name is required.',
            'bank_details.ifsc_code.required' => 'IFSC code is required.',
            'bank_details.account_number.required' => 'Account number is required.',

            /* ONDC */
            'role_selection_details.domain.required' => 'Please select at least one ONDC domain.',
            'role_selection_details.transaction_type.required' => 'Transaction type is required.',

            /* Value Proposition */
            'value_proposition_details.short_description.required' => 'Short description is required.',
            'value_proposition_details.additional_services.required' => 'Additional services field is required.',
            'value_proposition_details.languages_supported.required' => 'Please select supported languages.',
            'value_proposition_details.team_scheme_landing_page.required' => 'Landing page link is required.',
            'value_proposition_details.short_video_pitch.required' => 'Short video pitch is required.',
            'value_proposition_details.flyer.required' => 'Flyer / presentation is required.',

            /* Commercial */
            'commercial_model_details.other_fees.required' => 'Other fees details are required.',
            'commercial_model_details.special_offers.required' => 'Special offers details are required.',
            'commercial_model_details.team_scheme_offers.required' => 'Team scheme offers are required.',
            'commercial_model_details.commercial_model_link.required' => 'Commercial model link is required.',

            /* Declaration */
            'declaration.accepted' => 'You must accept the declaration to proceed.',
        ];
    }
}
