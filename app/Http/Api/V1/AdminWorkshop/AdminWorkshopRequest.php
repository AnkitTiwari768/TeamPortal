<?php

namespace App\Http\Api\V1\AdminWorkshop;

use Illuminate\Foundation\Http\FormRequest;

class AdminWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $districtId = request()->input('district_id');
        $hasSubDistrict = false;
        if ($districtId) {
            $hasSubDistrict = \DB::table('sub_districts')
                ->where('district_id', $districtId)
                ->exists();
        }
        return [
            // Mandatory
            'financial_year'         => 'required|string|max:20',
            'duration'               => 'required|string|max:50',
            'has_sub_duration'       => 'nullable|in:0,1',
            'sub_duration'           => 'required_if:has_sub_duration,1|nullable|string|max:50',
            'workshop_title'         => 'required|string|max:255',
            'workshop_date'          => 'required|date_format:d-m-Y',
            'state_id'               => 'required|uuid',
            'district_id'            => 'required|uuid',
            'venue'                  => 'required|string|max:255',
            'workshop_mode'          => 'required|string|max:100',
            'organizer_name'         => 'required|string|max:255',
            'expense_amount'         => 'required|numeric|min:0.01',
            'tds_applicable'         => 'nullable|in:0,1',

            // Conditional: tds_percentage required ONLY when tds_applicable = 1
            'tds_percentage'         => 'required_if:tds_applicable,1|nullable|numeric|min:0|max:99.99',

            // Optional
            'conducted_by'           => 'nullable|string|max:255',
            'target_audience'        => 'nullable|string|max:255',
            'number_of_participants' => 'nullable|integer|min:1',
            'workshop_description'   => 'nullable|string',
            'sanction_order_number'  => 'nullable|string|max:255',
            'sanction_order_date'    => 'nullable|date_format:d-m-Y',
            'remarks'                => 'nullable|string|max:500',
            'uploaded_ids'           => 'nullable|string',
            'status'                 => 'nullable|string|in:draft,published',
            'net_amount'             => 'nullable|numeric|min:0', // Calculated field, but validate if provided
            // Schedules (optional nested)
            'schedules'              => 'nullable|array',
            'schedules.*.start_time' => 'required_with:schedules|date_format:Y-m-d H:i:s',
            'schedules.*.end_time'   => 'required_with:schedules|date_format:Y-m-d H:i:s|after:schedules.*.start_time',

            //'sub_district_id'=>'required|uuid',
            'sub_district_id' => $hasSubDistrict
            ? 'required|uuid'
            : 'nullable',

            'branch_office'=>'required|uuid',

            'conducted_by_other'=>'nullable',

            'nsic_fees'=>'nullable',

        ];

        // NOTE: net_amount is intentionally excluded — it is calculated server-side only
    }

    public function messages(): array
    {
        return [
            'financial_year.required'         => __('workshop.validation.financial_year_required'),
            'duration.required'               => __('workshop.validation.duration_required'),
            'sub_duration.required_if'        => __('workshop.validation.sub_duration_required_if'),
            'workshop_title.required'         => __('workshop.validation.workshop_title_required'),
            'workshop_title.max'              => __('workshop.validation.workshop_title_max'),
            'workshop_date.required'          => __('workshop.validation.workshop_date_required'),
            'workshop_date.date'              => __('workshop.validation.workshop_date_date'),
            'state_id.required'               => __('workshop.validation.state_id_required'),
            'state_id.uuid'                   => __('workshop.validation.state_id_uuid'),
            'district_id.required'            => __('workshop.validation.district_id_required'),
            'district_id.uuid'                => __('workshop.validation.district_id_uuid'),
            'venue.required'                  => __('workshop.validation.venue_required'),
            'workshop_mode.required'          => __('workshop.validation.workshop_mode_required'),
            'organizer_name.required'         => __('workshop.validation.organizer_name_required'),
            'expense_amount.required'         => __('workshop.validation.expense_amount_required'),
            'expense_amount.numeric'          => __('workshop.validation.expense_amount_numeric'),
            'expense_amount.min'              => __('workshop.validation.expense_amount_min'),
            'tds_applicable.required'         => __('workshop.validation.tds_applicable_required'),
            'tds_applicable.in'               => __('workshop.validation.tds_applicable_in'),
            'tds_percentage.required_if'      => __('workshop.validation.tds_percentage_required_if'),
            'tds_percentage.numeric'          => __('workshop.validation.tds_percentage_numeric'),
            'tds_percentage.max'              => __('workshop.validation.tds_percentage_max'),
            'number_of_participants.integer'  => __('workshop.validation.number_of_participants_integer'),
            'number_of_participants.min'      => __('workshop.validation.number_of_participants_min'),
            'sanction_order_date.date'        => __('workshop.validation.sanction_order_date_date'),
            'remarks.max'                     => __('workshop.validation.remarks_max'),
            'status.in'                       => __('workshop.validation.status_in'),
            'net_amount.numeric'              => __('workshop.validation.net_amount_numeric'),
            'net_amount.min'                  => __('workshop.validation.net_amount_min'),

            'sub_district_id.required'        => __('workshop.validation.sub_district_id_required'),
            'branch_office.required'          => __('workshop.validation.branch_office_required'),
        ];
    }

    /**
     * Static proxy — allows Validator::make() callers to remain compatible.
     * Prefer FormRequest injection over this in new code.
     */
    public static function getRules(): array
    {
        return (new self())->rules();
    }

    public static function getMessages(): array
    {
        return (new self())->messages();
    }
}
