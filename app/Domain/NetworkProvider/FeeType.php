<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

enum FeeType: string
{
    case FLAT_FEE = 'flat_fee';
    case SUBSCRIPTION = 'subscription';

    public function label(): string
    {
        return match ($this) {
            self::FLAT_FEE     => 'Flat Fee',
            self::SUBSCRIPTION => 'Subscription',
        };
    }
}
