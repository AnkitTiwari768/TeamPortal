<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use Illuminate\Foundation\Http\FormRequest;

class FundDistributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $durationId = $this->input('duration_id');
        $isYearly = true;
        if (!empty($durationId)) {
            $duration = \Illuminate\Support\Facades\DB::table('attribute_values')->where('id', $durationId)->first();
            if ($duration) {
                $name = strtolower(trim((string) ($duration->name ?? $duration->attribute_value ?? '')));
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
                'exists:attribute_values,id',
            ],
            'sub_duration_id' => $isYearly ? [
                'bail',
                'nullable',
                'string',
                'exists:attribute_values,id',
            ] : [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'major_component_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'sub_component_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'distribution_amount' => [
                'bail',
                'required',
                'numeric',
                'gt:0',
            ],
            'tds_percentage' => [
                'bail',
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
            'sanction_order_number' => [
                'bail',
                'nullable',
                'string',
                'max:100',
            ],
            'sanction_order_date' => [
                'bail',
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (empty($value)) {
                        return;
                    }
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
            'upload_document' => [
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
        ];
    }

    public function messages(): array
    {
        return [
            'financial_year.required' => __('fund_flow.validation.financial_year_required'),
            'financial_year.regex' => __('fund_flow.validation.financial_year_regex'),
            'duration_id.required' => __('fund_flow.validation.duration_id_required'),
            'sub_duration_id.required' => 'The Sub Duration is required.',
            'major_component_id.required' => 'The Component is required.',
            'sub_component_id.required' => 'Component Field is required.',
            'distribution_amount.required' => 'The Amount is required.',
            'distribution_amount.gt' => 'Distribution Amount must be greater than zero.',
            'tds_percentage.required' => 'TDS percentage is required (put 0 if none).',
            'tds_percentage.min' => 'TDS percentage cannot be negative.',
            'tds_percentage.max' => 'TDS percentage cannot exceed 100.',
            'sanction_order_date.date_format' => 'The date must match format dd-mm-yyyy.',
        ];
    }
}
