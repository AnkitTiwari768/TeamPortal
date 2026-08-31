<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Generate a secure random password
     */
    public static function generate(
        int $length = 12,
        bool $useUppercase = true,
        bool $useLowercase = true,
        bool $useNumbers = true,
        bool $useSymbols = true
    ): string {
        $characters = '';

        if ($useUppercase) {
            $characters .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }

        if ($useLowercase) {
            $characters .= 'abcdefghijklmnopqrstuvwxyz';
        }

        if ($useNumbers) {
            $characters .= '0123456789';
        }

        if ($useSymbols) {
            $characters .= '!@#$%^&*()-_=+[]{}<>?';
        }

        if ($characters === '') {
            throw new \InvalidArgumentException('At least one character set must be enabled.');
        }

        return self::randomString($characters, $length);
    }

    /**
     * Generate cryptographically secure random string
     */
    protected static function randomString(string $characters, int $length): string
    {
        $password = '';
        $maxIndex = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
