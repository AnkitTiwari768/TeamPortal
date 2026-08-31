<?php

declare(strict_types=1);

namespace App\Enums;

enum Status: int
{
    case APPROVE = 1;
    case REVERT  = 2;
}
