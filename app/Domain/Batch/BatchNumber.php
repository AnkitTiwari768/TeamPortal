<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\ClaimType\ClaimTypeRepository;
use App\Domain\SNP\SNPRepository;
use App\Utils\Calendar;

final readonly class BatchNumber
{
    public static function generate(string $claimTypeId): string
    {

        $claimTypeShortName = app(ClaimTypeRepository::class)->getClaimTypeShortNameById($claimTypeId);
        if ($claimTypeShortName === 'AIC') {
            $snpId = '';
        } else {
            $snpId = app(SNPRepository::class)->getSnpIdByUserId(authId());
        }

        $month = Calendar::getCurrentMonth();
        $year = Calendar::getCurrentFinancialYear();
        $day   = now()->format('d');

        return $snpId
            . $claimTypeShortName
            . $year
            . $day
            . str_pad($month, 2, '0', STR_PAD_LEFT)
            . rand(1000, 9999);
    }
}
