<?php

declare(strict_types=1);

namespace App\Domain\IARegistration;

enum IAStatus: int
{
    case PENDING = 1;
    case APPROVE = 2;
    case REJECT = 3;
}
