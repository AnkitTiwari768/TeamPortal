<?php

declare(strict_types=1);

namespace App\Web\Allocation;

/**
 * AllocationRequest
 *
 * Centralized validation rules and messages for the Allocation Module.
 */
class AllocationRequest
{
    public static function getRules(?string $id = null): array
    {
        return [
            // ── Header Fields ─────────────────────────────────────────
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
            'sub_duration_id' => [
                'bail',
                'nullable',
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
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'opening_balance' => [
                'bail',
                'nullable',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
            'apply_carry_forward' => [
                'bail',
                'nullable',
                'boolean',
            ],

            // ── Allocation Lines ──────────────────────────────────────
            'allocation_lines' => ['required', 'array', 'min:1'],
            'allocation_lines.*.major_component_id' => ['bail', 'required', 'string', 'exists:attribute_values,id'],
            'allocation_lines.*.component_id' => ['bail', 'nullable', 'string', 'exists:attribute_values,id'],
            'allocation_lines.*.sub_component_id' => ['bail', 'nullable', 'string', 'exists:attribute_values,id'],

            // ── Amount: required on CREATE, nullable on EDIT ──────────
            'allocation_lines.*.amount' => $id ? [
                'bail',
                'nullable',
                'regex:/^\d+(\.\d{1,2})?$/',
            ] : [
                'bail',
                'required',
                'regex:/^\d+(\.\d{1,2})?$/',
            ],
        ];
    }

    public static function messages(): array
    {
        return [
            'financial_year.required' => 'Financial Year is required.',
            'financial_year.regex' => 'Financial Year must be in the format YYYY-YYYY.',
            'duration_id.required' => 'Duration is required.',
            'sanction_order_number.required' => 'Sanction Order Number is required.',
            'sanction_order_number.regex' => 'Sanction Order Number may only contain letters, numbers, spaces, / and -.',
            'sanction_order_date.required' => 'Sanction Order Date is required.',

            'allocation_lines.required' => 'At least one allocation line is required.',
            'allocation_lines.min' => 'At least one allocation line is required.',
            'allocation_lines.*.major_component_id.required' => 'Major Component is required in each allocation row.',
            'allocation_lines.*.major_component_id.exists' => 'Selected Major Component is invalid.',
            'allocation_lines.*.component_id.exists' => 'Selected Component is invalid.',
            'allocation_lines.*.sub_component_id.exists' => 'Selected Sub-Component is invalid.',
            'allocation_lines.*.amount.required' => 'Amount is required in each allocation row.',
            'allocation_lines.*.amount.regex' => 'Amount must be a valid number (e.g. 5000.00).',
        ];
    }
}