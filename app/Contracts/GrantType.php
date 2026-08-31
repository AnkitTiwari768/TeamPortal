<?php declare(strict_types=1);

namespace App\Contracts;

interface GrantType 
{
    public const THROUGH_ROLE = 1;
    public const THROUGH_USER = 2;
    public const THROUGH_AUXILIARY_ROLE = 3;
}