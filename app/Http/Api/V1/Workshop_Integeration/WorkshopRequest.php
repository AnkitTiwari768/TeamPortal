<?php 
declare(strict_types=1);
namespace App\Http\Api\V1\Workshop_Integeration;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; 


class WorkshopRequest
{
    public static function getRules(?string $id = null): array
    {
        return [

            'event_title' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/'
            ],

            'event_for' => [
                'bail',
                'required',
                'string',
                'max:255'
            ],

            'organiser_name' => [
                'bail',
                'required',
                'string',
                'min:1',
                'max:255',
                'regex:/^[a-zA-Z0-9\s]+$/'
            ],

            'remark' => [
                'nullable',
                'string',
                'max:300'
            ],

            'venue_address' => [
                'nullable',
                'string',
                'max:300'
            ],

            'event_description' => [
                'nullable',
                'string',
                'max:300'
            ],

            'uploaded_ids' => 'nullable|string',
            'start_date.*' => 'required|date',
            'end_date.*'   => 'required|date|after_or_equal:start_date.*',
            'start_time.*' => 'required|date_format:H:i',

            'state_id' => [
                'nullable',
                'string',
                'size:36'
            ],

            'district_id' => [
                'nullable',
                'string',
                'size:36'
            ],

            'pincode' => [
                'nullable',
                'digits_between:4,10'
            ],

            'latitude' => [
                'nullable',
                'string',
                'max:15'
            ],

            'longitude' => [
                'nullable',
                'string',
                'max:15'
            ],
        ];
    }

    public static function messages(): array
    {
        return [

            'event_title.required' => 'Event title is required.',
            'event_title.string' => 'Event title must be a valid text.',
            'event_title.min' => 'Event title must contain at least 1 character.',
            'event_title.max' => 'Event title may not be more than 255 characters.',
            'event_title.regex' => 'Event title may only contain letters, numbers, and spaces.',

            'event_for.required' => 'Please select event for.',
            'event_for.string' => 'Event for must be valid text.',
            'event_for.max' => 'Event for may not exceed 255 characters.',

            'organiser_name.required' => 'Organiser name is required.',
            'organiser_name.string' => 'Organiser name must be valid text.',
            'organiser_name.min' => 'Organiser name must contain at least 1 character.',
            'organiser_name.max' => 'Organiser name may not exceed 255 characters.',
            'organiser_name.regex' => 'Organiser name may only contain letters, numbers, and spaces.',

            'remark.max' => 'Remark may not exceed 300 characters.',
            'venue_address.required' => 'Venue address is required.',

            'event_description.max' => 'Event description may not exceed 300 characters.',

            'start_date.*.required' => 'Start date is required.',
            'start_date.*.date' => 'Start date must be a valid date.',

            // END DATE
            'end_date.*.required' => 'End date is required.',
            'end_date.*.date' => 'End date must be a valid date.',
            'end_date.*.after_or_equal' => 'End date must be greater than or equal to start date.',

            // START TIME
            'start_time.*.required' => 'Start time is required.',
            'start_time.*.date_format' => 'Start time must be in valid time format (HH:MM).',

            'state_id.required' => 'Please select a state.',
            'state_id.string' => 'State value must be a valid string.',
            'state_id.size' => 'State ID must be 36 characters (UUID).',

            'district_id.required' => 'Please select a district.',
            'district_id.string' => 'District value must be a valid string.',
            'district_id.size' => 'District ID must be 36 characters (UUID).',

            'pincode.required' => 'Pincode is required.',
            'pincode.digits_between' => 'Pincode must be between 4 to 10 digits.',

            'latitude.max' => 'Latitude may not exceed 15 characters.',

            'longitude.max' => 'Longitude may not exceed 15 characters.',

            'uploaded_ids.required' => 'Upload poster is required.',
        ];
    }
}
