<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class OtpGenerator
{
    public static function generateOtp(): int
    {
        return random_int(100000, 999999);
    }
}
