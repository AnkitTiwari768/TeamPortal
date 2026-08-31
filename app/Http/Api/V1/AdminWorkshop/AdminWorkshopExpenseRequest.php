<?php

namespace App\Http\Api\V1\AdminWorkshop;

use Illuminate\Foundation\Http\FormRequest;

class AdminWorkshopExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_amount'      => 'required|numeric|min:0.01',
            'tds_applicable'      => 'required|in:0,1',
            'tds_percentage'      => 'required_if:tds_applicable,1|nullable|numeric|min:0|max:100',
            'component_id'        => 'required|uuid',
            'subcomponent_id'     => 'required|uuid',
            'sanction_order_no'   => 'nullable|string|max:255',
            'sanction_order_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'expense_amount.required'    => 'Expense amount is required.',
            'expense_amount.min'         => 'Expense amount must be greater than zero.',
            'tds_applicable.required'    => 'Please indicate if TDS is applicable.',
            'tds_percentage.required_if' => 'TDS percentage is required when TDS is applicable.',
            'component_id.required'      => 'Component is required.',
            'component_id.uuid'          => 'Invalid component selected.',
            'subcomponent_id.required'   => 'Sub-component is required.',
            'subcomponent_id.uuid'       => 'Invalid sub-component selected.',
        ];
    }
}
