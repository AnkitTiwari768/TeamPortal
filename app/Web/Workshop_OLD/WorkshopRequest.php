<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;   // <-- add this

class WorkshopRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'financial_year'    => 'required|string',
            'duration'          => 'required|string',
             'sub_duration' => [
                 'nullable',
                function ($attribute, $value, $fail) {
                    $duration = $this->input('duration');
                    // Only require sub_duration if duration is set and not "Yearly"/"yearly"
                    if (!empty($duration) && !in_array($duration, ['Yearly', 'yearly']) && empty($value)) {
                        $fail(__('workshop.validation.sub_duration_required_if'));
                    }
                },
            ],
            'event_title'       => 'required|string|min:1|max:255',
            'event_for'         => 'required|array',
            'event_for.*'       => 'required|string',
            'workshop_category' => 'required|string',
            'remark'            => 'nullable|string|max:300',
            'organiser_name'    => 'required|string|min:1|max:255',
            'branch_office'     => 'required|string',
            'start_date'        => 'required|array',
            'start_date.*'      => 'required|date_format:d-m-Y',
            'end_date'          => 'required|array',
            'end_date.*'        => 'required|date_format:d-m-Y|after_or_equal:start_date.*',
            'start_time'        => 'required|array',
            'start_time.*'      => 'required',
            'event_description' => 'required|string|max:300',
            'workshop_mode'     => 'required|string',
            'conducted_by'      => 'nullable|string',
            'target_audience'   => 'nullable|string',
            'venue_address'     => 'required|string',
            'state_id'          => 'required|uuid',
            'district_id'       => 'required|uuid',
            'sub_district_id'   => 'required|uuid',
            'pincode'           => 'nullable|digits:6',
            'latitude'          => 'nullable|string|max:15',
            'longitude'         => 'nullable|string|max:15',
            'uploaded_ids'      => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'financial_year.required'    => __('workshop.validation.financial_year_required'),
            'duration.required'          => __('workshop.validation.duration_required'),
            'sub_duration.required_unless' => __('workshop.validation.sub_duration_required_if'),
            'event_title.required'       => __('workshop.validation.event_title_required'),
            'event_title.string'         => __('workshop.validation.event_title_string'),
            'event_title.min'            => __('workshop.validation.event_title_min'),
            'event_title.max'            => __('workshop.validation.event_title_max'),
            'event_title.regex'          => __('workshop.validation.event_title_regex'),
            'event_for.required'         => __('workshop.validation.event_for_required'),
            'event_for.*.required'       => __('workshop.validation.event_for_required'),
            'workshop_category.required' => __('workshop.validation.workshop_category_required'),
            'remark.max'                 => __('workshop.validation.remark_max'),
            'organiser_name.required'    => __('workshop.validation.organiser_name_required'),
            'organiser_name.string'      => __('workshop.validation.organiser_name_string'),
            'organiser_name.min'         => __('workshop.validation.organiser_name_min'),
            'organiser_name.max'         => __('workshop.validation.organiser_name_max'),
            'organiser_name.regex'       => __('workshop.validation.organiser_name_regex'),
            'branch_office.required'     => __('workshop.validation.branch_office_required'),
            'start_date.required'        => __('workshop.validation.start_date_required'),
            'start_date.*.required'      => __('workshop.validation.start_date_required'),
            'start_date.*.date_format'   => __('workshop.validation.start_date_date'),
            'end_date.required'          => __('workshop.validation.end_date_required'),
            'end_date.*.required'        => __('workshop.validation.end_date_required'),
            'end_date.*.date_format'     => __('workshop.validation.end_date_date'),
            'end_date.*.after_or_equal'  => __('workshop.validation.end_date_after_or_equal'),
            'start_time.required'        => __('workshop.validation.start_time_required'),
            'start_time.*.required'      => __('workshop.validation.start_time_required'),
            'event_description.required' => __('workshop.validation.event_description_required'),
            'event_description.max'      => __('workshop.validation.event_description_max'),
            'workshop_mode.required'     => __('workshop.validation.workshop_mode_required'),
            'venue_address.required'     => __('workshop.validation.venue_address_required'),
            'state_id.required'          => __('workshop.validation.state_required'),
            'state_id.uuid'              => __('workshop.validation.state_size'),
            'district_id.required'       => __('workshop.validation.district_required'),
            'district_id.uuid'           => __('workshop.validation.district_size'),
            'sub_district_id.required'   => __('workshop.validation.sub_district_id_required'),
            'pincode.digits'             => __('workshop.validation.pincode_digits'),
            'latitude.max'               => __('workshop.validation.latitude_max'),
            'longitude.max'              => __('workshop.validation.longitude_max'),
            'uploaded_ids.required'      => __('workshop.validation.uploaded_ids_required'),
        ];
    }

    /**
     * Configure the validator instance – add custom financial‑year check.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $financialYear = $this->input('financial_year');
            if (!$financialYear) {
                return;
            }

            $years = explode('-', $financialYear);
            if (count($years) !== 2) {
                return;
            }

            [$startYear, $endYear] = $years;
            $allowedYears = [(int)$startYear, (int)$endYear];

            $startDates = $this->input('start_date', []);
            $endDates   = $this->input('end_date', []);

            foreach ($startDates as $index => $startDateStr) {
                // Validate start date year
                try {
                    $startDate = Carbon::createFromFormat('d-m-Y', $startDateStr);
                } catch (\Exception $e) {
                    $validator->errors()->add("start_date.{$index}", __('workshop.invalid_start_date'));
                    continue;
                }

                if (!in_array($startDate->year, $allowedYears)) {
                    $validator->errors()->add(
                        "start_date.{$index}",
                        __('workshop.start_date_year_out_of_fy', ['fy' => $financialYear])
                    );
                }

                // Validate end date year
                $endDateStr = $endDates[$index] ?? null;
                if ($endDateStr) {
                    try {
                        $endDate = Carbon::createFromFormat('d-m-Y', $endDateStr);
                    } catch (\Exception $e) {
                        $validator->errors()->add("end_date.{$index}", __('workshop.invalid_end_date'));
                        continue;
                    }

                    if (!in_array($endDate->year, $allowedYears)) {
                        $validator->errors()->add(
                            "end_date.{$index}",
                            __('workshop.end_date_year_out_of_fy', ['fy' => $financialYear])
                        );
                    }
                }
            }
        });
    }

    /**
     * Map request data to DTO.
     */
    public function toDto(): CreateWorkshopDTO
    {
        return CreateWorkshopDTO::fromArray($this->validated());
    }
}