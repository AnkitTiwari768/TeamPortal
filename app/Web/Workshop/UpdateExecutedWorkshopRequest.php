<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateExecutedWorkshopRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Base rules: status and remark are optional
        $rules = [
            'status' => [
                'nullable',
                'in:New,Completed,Cancelled',
                function ($attribute, $value, $fail) {
                    if ($value !== 'Cancelled') {
                        return;
                    }
                    $workshopId = $this->route('id');
                    if (empty($workshopId)) {
                        return;
                    }
                    $currentStatus = DB::table('workshops')->where('id', $workshopId)->value('status');
                    if ($currentStatus === 'Completed') {
                        $fail('A Completed workshop cannot be changed to Cancelled.');
                    }
                },
            ],
            'remark' => 'nullable|string|max:1000',
        ];

        // Determine context: edit page (no status) or modal (has status)
        $isEdit = !$this->has('status');
        $isCompleted = $this->input('status') === 'Completed';

        // Expense fields are required only for edit page OR when status is Completed
        if ($isEdit || $isCompleted) {
            $rules['no_of_participants'] = 'required|integer|min:1';
            $rules['expense_amount'] = 'required|numeric|min:0.01';
            $rules['nsic_fee'] = 'required|numeric|min:0';
            $rules['tds_applicable'] = 'required|in:Yes,No';
            $rules['tds_percentage'] = 'required_if:tds_applicable,Yes|nullable|numeric|min:0|max:100';
            $rules['sanction_order_number'] = 'required|string|max:255';
            $rules['sanction_order_date'] = 'required|date_format:d-m-Y';
            $rules['supporting_document'] = 'nullable|string|max:255';
            $rules['remarks'] = 'nullable|string|max:1000';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            // ... your existing messages ...
            // Add these if needed:
            'status.in' => 'Invalid status selected.',
            'remark.max' => 'Remark cannot exceed 1000 characters.',
        ];
    }

    public function toDto(): UpdateExecutedWorkshopDTO
    {
        return UpdateExecutedWorkshopDTO::fromArray($this->validated());
    }
}