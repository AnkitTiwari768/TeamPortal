<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

readonly class VerifyOtpDto
{
    public function __construct(
        public string $username,
        public string $otpCode,
    ) {}

    public static function fromArray(array $validated): self
    {
        return new self(
            username: $validated['username'],
            otpCode: $validated['otp']
        );
    }
}
