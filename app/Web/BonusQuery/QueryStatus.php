<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

enum QueryStatus: int
{
    case Open = 1;
    case Pending = 2;
    case Closed = 3;
    case Accept = 4;
    case Reject = 5;

    public static function getName(int $status): string
    {
        return match ($status) {
            1 => 'Open',
            2 => 'In-Progress',
            3 => 'Closed',
            4 => 'Accepted',
            5 => 'Rejected'
        };
    }
}
