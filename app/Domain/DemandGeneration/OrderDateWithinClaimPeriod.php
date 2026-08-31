<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;

class OrderDateWithinClaimPeriod implements ValidationRule
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        try {
            $orderDate = Carbon::parse($value);

            if (!$this->start || !$this->end) {
                $fail('Claim period is missing.');
                return;
            }

            $startDate = Carbon::parse($this->start)->startOfDay();
            $endDate   = Carbon::parse($this->end)->endOfDay();

            if ($orderDate->lt($startDate) || $orderDate->gt($endDate)) {
                $fail("Order creation timestamp must be between {$startDate->format('d-m-Y')} and {$endDate->format('d-m-Y')}.");
            }
        } catch (\Exception $e) {
            $fail('Invalid timestamp format.');
        }
    }
}
