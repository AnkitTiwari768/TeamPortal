<?php

namespace App\Domain\DemandGeneration\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class NoClaimOverlap implements ValidationRule
{
    public function __construct(
        protected string $claimTypeSlug,
        protected string $startDateField,
        protected string $endDateField
    ) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $request = request();
        $startDate = $request->input($this->startDateField);
        $endDate = $request->input($this->endDateField);

        if (!$startDate || !$endDate) {
            return; // Missing dates will be caught by other validation rules
        }

        $userId = auth()->id();
        $claimTypeId = DB::table('claim_types')->where('slug', $this->claimTypeSlug)->value('id');

        if (!$claimTypeId || !$userId) {
            return;
        }

        $overlapExists = DB::table('claims')
            ->where('created_by', $userId)
            ->where('claim_type_id', $claimTypeId)
            // exclude rejected statuses if necessary, but the request didn't specify. 
            // usually REJECTED claims can be re-submitted for same period, if we need to ignore them 
            // we would add something like ->where('status', '!=', ClaimReviewStatus::REJECTED->value)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('claim_period_end_date', '>=', $startDate)
                      ->where('claim_period_start_date', '<=', $endDate);
            })
            ->exists();

        if ($overlapExists) {
            $fail('The claim date period overlaps partially or fully with an existing claim. Please choose a valid non-overlapping period.');
        }
    }
}
