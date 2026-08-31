<?php

declare(strict_types=1);

namespace App\Domain\DemandGeneration;

final readonly class DemandGenerationConstant
{
    public const CLAIM_PERIOD_DAYS_LIMIT = 60;
    public const LOW_AOV_MINIMUM_ORDER_VALUE = 10;
    public const HIGH_AOV_MINIMUM_ORDER_VALUE = 10;
}
