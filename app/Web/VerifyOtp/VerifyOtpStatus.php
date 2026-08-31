<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

enum VerifyOtpStatus: int
{
    case CODE_INVALID = 1;
    case CODE_EXPIRED = 2;
    case CODE_VERIFIED = 3;
}
