<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserStatus: int
{
    case ACTIVE = 1;
    case INACTIVE = 0;
}
