<?php

declare(strict_types=1);

namespace App\Utils;

use Carbon\Carbon;

final readonly class Calendar
{
    public static function getCurrentFinancialYear(): string
    {
        $today = Carbon::now();

        return ($today->month >= 4)
            ? $today->year . '-' . ($today->year + 1)
            : ($today->year - 1) . '-' . $today->year;
    }

    public static function getCurrentMonth(): string
    {
        return Carbon::now()->format('m');
    }
}
