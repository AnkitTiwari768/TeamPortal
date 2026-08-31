<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Illuminate\Foundation\Http\FormRequest;

class DemandGenerationClaimRequest extends FormRequest
{
    use ValidatesClaimPeriod;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'low_aov_categories' => ['nullable', 'array', 'min:1'],
            'low_aov_categories.*' => ['exists:sub_domains,id'],
            'low_aov_unique_mse' => ['nullable', 'integer', 'min:1'],
            'low_aov_cumulative_txn' => ['nullable', 'integer', 'min:1'],
            'low_aov_claim_period_start' => ['nullable', 'date'],
            'low_aov_claim_period_end' => ['nullable', 'date', 'after_or_equal:low_aov_claim_period_start'],
            'low_aov_claim_period' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $this->validateClaimPeriod(
                        $this->input('low_aov_claim_period_start'),
                        $this->input('low_aov_claim_period_end'),
                        $fail
                    );
                }
            ],

            'high_aov_categories' => ['sometimes', 'array'],
            'high_aov_categories.*' => ['exists:sub_domains,id'],
            'high_aov_unique_mse' => ['nullable', 'required_with:high_aov_categories', 'integer', 'min:0'],
            'high_aov_cumulative_txn' => ['nullable', 'required_with:high_aov_categories', 'integer', 'min:0'],
            'high_aov_claim_period_start' => ['nullable', 'required_with:high_aov_categories', 'date'],
            'high_aov_claim_period_end' => ['nullable', 'required_with:high_aov_categories', 'date', 'after_or_equal:high_aov_claim_period_start'],
            'high_aov_claim_period' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $this->validateClaimPeriod(
                        $this->input('low_aov_claim_period_start'),
                        $this->input('low_aov_claim_period_end'),
                        $fail
                    );
                }
            ],

            'declaration' => ['required', 'accepted'],

            // File validation with column count check
            'low_aov_excel' => [
                'nullable',
                'file',
                'mimes:xlsx,xls,csv',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $this->validateExcelColumns($value, $fail, 'Low AOV');
                }
            ],
            'high_aov_excel' => [
                'nullable',
                'file',
                'mimes:xlsx,xls,csv',
                'max:5120',
                function ($attribute, $value, $fail) {
                    if ($value) {
                        $this->validateExcelColumns($value, $fail, 'High AOV');
                    }
                }
            ],
        ];
    }

    private function validateExcelColumns($file, $fail, $type)
    {
        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();

            // Check header row (row 1)
            $expectedHeaders = [
                'Network Transaction ID',
                'Network Transaction Date',
                'Seller ID (bppId.providerId)',
                'Transaction Status (Completed Yes/No)',
                'Order Invoice Number',
                'Has MSME TEAM Cred (Yes/No)',
                'Total Product Cost',
                'Total Tax on Product',
                'Total Discount on Products (If Any)',
                'Offers (If Any)',
                'Logistics + Packaging Charges',
                'Tax on Delivery & Packaging',
                'Misc (Convenience fee etc.)'
            ];

            $actualHeaders = [];
            for ($col = 0; $col < count($expectedHeaders); $col++) {
                $cellValue = $worksheet->getCellByColumnAndRow($col + 1, 1)->getValue();
                $actualHeaders[] = trim((string) $cellValue);
            }

            // Compare headers
            for ($i = 0; $i < count($expectedHeaders); $i++) {
                if ($actualHeaders[$i] !== $expectedHeaders[$i]) {
                    $fail("{$type} Excel file column " . ($i + 1) . " should be '{$expectedHeaders[$i]}', but found '{$actualHeaders[$i]}'. Please download and use the provided template.");
                    return;
                }
            }

            // Check if file has any data rows
            $highestRow = $worksheet->getHighestRow();
            if ($highestRow <= 1) {
                $fail("{$type} Excel file contains only headers. Please add transaction data.");
            }
        } catch (\Exception $e) {
            $fail("Failed to validate {$type} Excel file: " . $e->getMessage());
        }
    }
}
