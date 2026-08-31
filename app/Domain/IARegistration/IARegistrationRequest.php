<?php

namespace App\Domain\IARegistration;

use Illuminate\Foundation\Http\FormRequest;

class IARegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'uuid'],
            // Basic Details
            'organization_name'           => 'required|string|min:8|max:100',
            'entity_type'                 => 'required|string',
            'entity_email'                => 'required|email|unique:users,email|unique:industrial_associations,entity_email',

            'number_of_members'           => 'nullable|integer|min:1',
            'registration_number'         => 'nullable|string|min:5|max:100',
            'website'                     => 'nullable|string|regex:/^(https?:\/\/)?([\w\-]+\.)+[\w\-]{2,}(\/\S*)?$/|max:255',
            'pan_number' => [
                'nullable',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'
            ],

            'contact_number'              => 'required|digits_between:10,15',

            // Address
            'state_id'                    => 'required|uuid',
            'district_id'                 => 'required|uuid',
            'complete_address'            => 'required|string|max:500',

            // Contact Person
            'contact_person_name'         => 'required|string|min:3|max:255',
            'contact_person_designation'  => 'required|string|min:3|max:255',
            'contact_person_phone'        => 'required|digits_between:10,15',
            'contact_person_email'        => 'required|email|max:255',

            // Document
            'authorization_document'      => 'required|string|exists:file_uploads,file_system_name',
            'authorization_document_id'   => 'nullable|exists:file_uploads,id',
        ];
    }

    public function messages(): array
    {
        return [
            'organization_name.required'          => 'Name of Association/Organisation is required.',
            'organization_name.string'            => 'Organization name must be valid text.',

            'entity_type.required'                => 'Please select Entity Type.',

            'entity_email.required'               => 'Email of Entity is required.',
            'entity_email.email'                  => 'Please enter a valid Entity Email.',

            'number_of_members.integer'            => 'Number of members must be a number.',
            'number_of_members.min'                => 'Number of members must be at least 1.',

            'registration_number.max'              => 'Registration number is too long.',

            'website.url'                          => 'Please enter a valid website URL.',

            'pan_number.regex' => 'Enter a valid PAN number (e.g. ABCDE1234F).',

            'contact_number.required'              => 'Contact number is required.',
            'contact_number.digits_between'        => 'Contact number must be between 10 to 15 digits.',

            'state_id.required'                    => 'Please select State.',
            'district_id.required'                 => 'Please select District.',

            'complete_address.required'            => 'Complete address is required.',

            'contact_person_name.required'         => 'Contact person name is required.',
            'contact_person_designation.required'  => 'Designation of contact person is required.',

            'contact_person_phone.required'        => 'Contact person phone number is required.',
            'contact_person_phone.digits_between'  => 'Phone number must be between 10 to 15 digits.',

            'contact_person_email.required'        => 'Contact person email is required.',
            'contact_person_email.email'           => 'Please enter a valid contact person email.',

            'authorization_document.required'      => 'Authorization Certificate & KYC document is required.',
            'authorization_document.mimes'         => 'Document must be PDF, JPG, JPEG or PNG.',
            'authorization_document.max'           => 'Document size must not exceed 2 MB.',
        ];
    }
}
