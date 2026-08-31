<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Carbon\Carbon;

trait ValidatesClaimPeriod
{
    protected function validateClaimPeriod(
        string $start,
        string $end,
        \Closure $fail
    ): void {
        if (!$start || !$end) {
            return;
        }

        $startDate = Carbon::parse($start);
        $endDate   = Carbon::parse($end);

        if ($endDate->diffInDays($startDate) > DemandGenerationConstant::CLAIM_PERIOD_DAYS_LIMIT) {
            $fail('Claim period cannot exceed ' . DemandGenerationConstant::CLAIM_PERIOD_DAYS_LIMIT . ' days.');
        }

        if ($endDate->isFuture()) {
            $fail('Claim period cannot be in the future.');
        }
    }
}
