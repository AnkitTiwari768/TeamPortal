<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

class FundFlowHelper
{
    public static function getFinancialYearString(): string
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');
        $startYear = $currentMonth >= 4 ? $currentYear : $currentYear - 1;
        $endYear = ($startYear + 1) % 100;
        $financialYearString = $startYear . '-' . sprintf('%02d', $endYear);
        return $financialYearString;
    }
}
