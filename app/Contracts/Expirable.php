<?php 

declare(strict_types=1);

namespace App\Contracts;

interface Expirable 
{
    public const EXPIRED = 1;
    public const NOT_EXPIRED = 0;
}
