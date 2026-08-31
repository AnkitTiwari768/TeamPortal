<?php

declare(strict_types=1);

namespace App\Domain\NetworkProvider;

enum NetworkProviderStatus: int
{
    case PENDING = 1;
    case APPROVE = 2;
    case REJECT = 3;
    case REVERT = 4;
}
