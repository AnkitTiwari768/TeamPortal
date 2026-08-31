<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExecutedWorkshopRequest extends FormRequest
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
            'no_of_participants'     => 'required|integer|min:1',
            'expense_amount'         => 'required|numeric|min:0.01',
            'nsic_fee'               => 'required|numeric|min:0',
            'tds_applicable'         => 'required|in:Yes,No',
            'tds_percentage'         => 'required_if:tds_applicable,Yes|nullable|numeric|min:0|max:100',
            'net_amount'             => 'nullable|numeric|min:0',
            'sanction_order_number'  => 'required|string|max:255',
            'sanction_order_date'    => 'required|date_format:d-m-Y',
            'supporting_document'    => 'nullable|string|max:255',
            'remarks'                => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get validation translation messages.
     */
    public function messages(): array
    {
        return [
            'no_of_participants.required'      => __('workshop.validation.number_of_participants_required'),
            'no_of_participants.integer'       => __('workshop.validation.number_of_participants_integer'),
            'no_of_participants.min'           => __('workshop.validation.number_of_participants_min'),
            'expense_amount.required'          => __('workshop.validation.expense_amount_required'),
            'expense_amount.numeric'           => __('workshop.validation.expense_amount_numeric'),
            'expense_amount.min'               => __('workshop.validation.expense_amount_min'),
            'nsic_fee.required'                => __('workshop.validation.nsic_fee_required'),
            'nsic_fee.numeric'                 => __('workshop.validation.nsic_fee_numeric'),
            'nsic_fee.min'                     => __('workshop.validation.nsic_fee_min'),
            'tds_applicable.required'          => __('workshop.validation.tds_applicable_required'),
            'tds_applicable.in'                => __('workshop.validation.tds_applicable_in'),
            'tds_percentage.required_if'       => __('workshop.validation.tds_percentage_required_if'),
            'tds_percentage.numeric'           => __('workshop.validation.tds_percentage_numeric'),
            'tds_percentage.max'               => __('workshop.validation.tds_percentage_max'),
            'sanction_order_number.required'   => __('workshop.validation.sanction_order_number_required'),
            'sanction_order_date.required'     => __('workshop.validation.sanction_order_date_required'),
            'sanction_order_date.date_format'  => __('workshop.validation.sanction_order_date_date'),
            'supporting_document.file'         => __('workshop.validation.supporting_document_file'),
            'supporting_document.mimes'        => __('workshop.validation.supporting_document_mimes'),
            'supporting_document.max'          => __('workshop.validation.supporting_document_max'),
            'remarks.max'                      => __('workshop.validation.remarks_max'),
        ];
    }

    /**
     * Map request data to DTO.
     */
    public function toDto(): UpdateExecutedWorkshopDTO
    {
        return UpdateExecutedWorkshopDTO::fromArray($this->validated());
    }
}
