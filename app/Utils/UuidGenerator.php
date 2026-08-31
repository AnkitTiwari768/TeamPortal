<?php

declare(strict_types=1);

namespace App\Utils;

use Illuminate\Support\Str;

class UuidGenerator
{
    public static function uuid7(): string
    {
        return (string) Str::orderedUuid();
    }
}
