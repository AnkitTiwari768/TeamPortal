<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Foundation\Http\FormRequest;

class FundAllocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id') ?? $this->input('id');

        $durationId = $this->input('duration_id');
        $isYearly = true;
        if (!empty($durationId)) {
            $duration = \Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $durationId)->first();
            if ($duration) {
                $name = strtolower(trim((string)($duration->name ?? $duration->attribute_value ?? '')));
                if ($name !== 'yearly') {
                    $isYearly = false;
                }
            }
        }

        return [
            'financial_year' => [
                'bail',
                'required',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}$/',
            ],
            'duration_id' => [
                'bail',
                'required',
                'string',
            ],
            'sub_duration_id' => $isYearly ? [
                'bail',
                'nullable',
                'string',
            ] : [
                'bail',
                'required',
                'string',
            ],
            'sanction_order_number' => [
                'bail',
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\/\-\s]+$/',
            ],
            'sanction_order_date' => [
                'bail',
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $fy = $this->input('financial_year');
                    if (empty($fy)) {
                        return;
                    }
                    $parts = explode('-', $fy);
                    if (count($parts) === 2) {
                        $startYear = (int) $parts[0];
                        $endYear = (int) $parts[1];
                        try {
                            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $value);
                            } else {
                                $date = \Carbon\Carbon::parse($value);
                            }
                        } catch (\Exception $e) {
                            $fail('Invalid sanction order date format.');
                            return;
                        }

                        $minDate = \Carbon\Carbon::create($startYear, 4, 1)->startOfDay();
                        $maxDate = \Carbon\Carbon::create($endYear, 3, 31)->endOfDay();

                        if ($date->lt($minDate) || $date->gt($maxDate)) {
                            $fail("The sanction order date must be within the selected financial year {$fy} (01-04-{$startYear} to 31-03-{$endYear}).");
                        }
                    }
                }
            ],
            'document_path' => [
                'bail',
                'nullable',
                'string',
            ],
            'remarks' => [
                'bail',
                'nullable',
                'string',
                'max:1000',
            ],
            'total_amount' => [
                'bail',
                'nullable',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
            ],
            'fresh_allocation_amount' => [
                'bail',
                'nullable',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
            ],
            'total_available_amount' => [
                'bail',
                'nullable',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
            ],
            'apply_carry_forward' => [
                'bail',
                'nullable',
                'boolean',
            ],
            'allocation_lines' => ['required', 'array', 'min:1'],
            'allocation_lines.*.major_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'allocation_lines.*.component_id' => ['bail', 'nullable', 'string', 'exists:attribute_values,id'],
            'allocation_lines.*.sub_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'allocation_lines.*.amount' => $id ? [
                'bail',
                'nullable',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
                'gt:0',
            ] : [
                'bail',
                'required',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
                'gt:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_year.required' => __('fund_flow.validation.financial_year_required'),
            'financial_year.regex' => __('fund_flow.validation.financial_year_regex'),
            'duration_id.required' => __('fund_flow.validation.duration_id_required'),
            'sanction_order_number.required' => __('fund_flow.validation.sanction_order_number_required'),
            'sanction_order_number.regex' => __('fund_flow.validation.sanction_order_number_regex'),
            'sanction_order_date.required' => __('fund_flow.validation.sanction_order_date_required'),
            'allocation_lines.required' => __('fund_flow.validation.allocation_lines_required'),
            'allocation_lines.min' => __('fund_flow.validation.allocation_lines_min'),
            'allocation_lines.*.major_component_id.required' => __('fund_flow.validation.major_component_required'),
            'allocation_lines.*.major_component_id.exists' => __('fund_flow.validation.major_component_exists'),
            'allocation_lines.*.component_id.exists' => __('fund_flow.validation.component_exists'),
            'allocation_lines.*.sub_component_id.required' => __('fund_flow.validation.sub_component_required'),
            'allocation_lines.*.sub_component_id.exists' => __('fund_flow.validation.sub_component_exists'),
            'allocation_lines.*.amount.required' => __('fund_flow.validation.amount_required'),
            'allocation_lines.*.amount.regex' => __('fund_flow.validation.amount_regex'),
            'allocation_lines.*.amount.gt' => 'Amount must be greater than 0.',
            'sub_duration_id.required' => 'Sub Duration is required.',
        ];
    }
}
