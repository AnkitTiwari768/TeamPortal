<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\ClaimType\ClaimTypeRepository;
use App\Utils\Calendar;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\DB;


class ValidateMonthlyBatch
{
    public function __invoke(Validator $validator): void
    {
        $data = $validator->getData();

        if (! config('settings.enable_batch_monthly_apply_validation')) return;

        if (empty($data['claim_type_id'])) return;

        $month = Calendar::getCurrentMonth();
        $year = Calendar::getCurrentFinancialYear();

        $claimTypeId = app(ClaimTypeRepository::class)->getClaimTypeIdBySlug($data['claim_type_id']);

        $exists = DB::table('dy_batches')
            ->where('claim_type_id', $claimTypeId)
            ->where('financial_year', $year)
            ->where('month', $month)
            ->where('created_by', authId())
            ->exists();

        if ($exists) {
            $validator->errors()->add(
                'claim_type_id',
                'A batch already exists for this claim type, month and financial year.'
            );
        }
    }
}
