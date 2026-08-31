<?php

declare(strict_types=1);

namespace App\Web\Workshop;

enum WorkshopStatus: string
{
    case CONFIRMED = 'Confirmed';
    case CANCELLED = 'Cancelled';
}
