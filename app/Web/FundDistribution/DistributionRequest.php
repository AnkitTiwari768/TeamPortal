<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

class DistributionRequest
{
    public static function getRules(): array
    {
        return [
            'financial_year' => [
                'required',
                'string',
                'regex:/^[0-9]{4}-[0-9]{4}$/'
            ],
            'duration_id' => [
                'required',
                'uuid',
                'exists:attribute_values,id'
            ],
            'sub_duration_id' => [
                'nullable',
                'uuid',
                'exists:attribute_values,id'
            ],
            'major_component_id' => [
                'required',
                'uuid',
                'exists:attribute_values,id'
            ],
            'sub_component_id' => [
                'nullable',
                'uuid',
                'exists:attribute_values,id'
            ],
            'distribution_amount' => [
                'required',
                'numeric',
                'gt:0'
            ],
            'tds_percentage' => [
                'required',
                'numeric',
                'min:0',
                'max:100'
            ],
            'sanction_order_number' => [
                'nullable',
                'string',
                'max:100'
            ],
            'sanction_order_date' => [
                'nullable',
                'date_format:d-m-Y'
            ],
            'upload_document' => [
                'nullable',
                'string'
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    public static function messages(): array
    {
        return [
            'distribution_amount.required' => 'The Distribution Amount is required.',
            'distribution_amount.gt' => 'Distribution Amount must be greater than zero.',
            'tds_percentage.required' => 'TDS percentage is required (put 0 if none).',
            'tds_percentage.min' => 'TDS percentage cannot be negative.',
            'tds_percentage.max' => 'TDS percentage cannot exceed 100.',
            'sanction_order_date.date_format' => 'The date must match format dd-mm-yyyy.'
        ];
    }
}
