<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Illuminate\Foundation\Http\FormRequest;

class FundCarryForwardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'financial_year' => [
                'bail',
                'required',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}$/',
            ],
            'from_duration_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'from_sub_duration_id' => [
                'bail',
                'nullable',
                'string',
                'exists:attribute_values,id',
            ],
            'to_duration_id' => [
                'bail',
                'required',
                'string',
                'exists:attribute_values,id',
            ],
            'to_sub_duration_id' => [
                'bail',
                'nullable',
                'string',
                'exists:attribute_values,id',
                'different:from_sub_duration_id',
            ],
            'carry_forward_date' => [
                'bail',
                'nullable',
                'date',
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
                'numeric',
            ],
            'carry_forward_lines' => ['required', 'array', 'min:1'],
            'carry_forward_lines.*.major_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'carry_forward_lines.*.sub_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'carry_forward_lines.*.opening_balance' => ['bail', 'nullable', 'numeric'],
            'carry_forward_lines.*.carried_forward_amount' => [
                'bail',
                'required',
                'regex:/^\d{1,16}(\.\d{1,2})?$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'financial_year.required' => __('fund_flow.validation_carry_forward.financial_year_required'),
            'financial_year.regex' => __('fund_flow.validation_carry_forward.financial_year_regex'),
            'from_duration_id.required' => __('fund_flow.validation_carry_forward.from_duration_id_required'),
            'to_duration_id.required' => __('fund_flow.validation_carry_forward.to_duration_id_required'),
            'carry_forward_lines.required' => __('fund_flow.validation_carry_forward.carry_forward_lines_required'),
            'carry_forward_lines.min' => __('fund_flow.validation_carry_forward.carry_forward_lines_required'),
            'carry_forward_lines.*.major_component_id.required' => __('fund_flow.validation_carry_forward.major_component_required'),
            'carry_forward_lines.*.sub_component_id.required' => __('fund_flow.validation_carry_forward.sub_component_required'),
            'carry_forward_lines.*.carried_forward_amount.required' => __('fund_flow.validation_carry_forward.amount_required'),
            'carry_forward_lines.*.carried_forward_amount.regex' => __('fund_flow.validation_carry_forward.amount_regex'),
            'to_sub_duration_id.different' => __('fund_flow.validation_carry_forward.duration_different'),
        ];
    }
}
