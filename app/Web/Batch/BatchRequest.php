<?php

declare(strict_types=1);

namespace App\Web\Batch;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;


class BatchRequest
{

    const ENABLE_MONTHLY_APPLY_VALIDATION = false;

    public static function getClaimTypeIdBySlug(string $slug): string
    {
        $claimType = \DB::table('claim_types')->where('slug', $slug)->first();
        if (!$claimType) {
            throw new \Exception("Claim type with slug '$slug' does not exist.");
        }
        return $claimType->id;
    }

    public static function getRules(?string $id = null): array
    {

        $claimType = self::getClaimTypeIdBySlug(request('claim_type_id'));

        // Current date
        $today = Carbon::now();

        // Calculate current month and financial year
        $month = $today->format('m'); // 01–12
        $year  = ($today->month >= 4)
            ? $today->year . '-' . ($today->year + 1)   // e.g. 2025-2026
            : ($today->year - 1) . '-' . $today->year;  // e.g. 2024-2025

        return [
            'claim_type_id' => [
                'required',
                'string',
                'exists:claim_types,slug',
                // custom unique closure
                function ($attribute, $value, $fail) use ($month, $year, $id) {
                    if (self::ENABLE_MONTHLY_APPLY_VALIDATION) {
                        $claimTypeId = BatchRequest::getClaimTypeIdBySlug($value);

                        $exists = \DB::table('batches')
                            ->where('claim_type_id', $claimTypeId)
                            ->where('financial_year', $year)
                            ->where('month', $month)
                            ->where('created_by', \Auth::id())
                            ->when($id, fn($q) => $q->where('id', '!=', $id))
                            ->exists();

                        if ($exists) {
                            $fail('A batch already exists for this claim type, month and financial year.');
                        }
                    }
                },
            ],

            /*'financial_year' => [
                Rule::unique('batches')
                    ->where(fn ($query) => $query
                        ->where('claim_type_id', $claimType)
                        ->where('financial_year', $year)
                        ->where('month', $month)
                    )
                    ->ignore($id),
            ],*/
            'claim_id' => [
                'required',
                'array',
            ],
            'claim_id.*' => [
                'required',
                'uuid',
                'exists:claims,id',
            ],
            'is_declaration_agreed' => [
                'nullable',
                'integer'
            ]
        ];
    }



    public static function messages(): array
    {
        return [
            'agreecheck.required' => 'The declaration field is required',
        ];
    }
}
