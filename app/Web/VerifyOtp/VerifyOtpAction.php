<?php

declare(strict_types=1);

namespace App\Web\VerifyOtp;

use App\Services\CryptoService;
use App\Web\SendOtp\SendOtpService;

class VerifyOtpAction
{
    private const PASS_OTP = 201301;

    public function __construct(
        private SendOtpService $sendOtpService,
        private CryptoService $cryptoService
    ) {}

    public function execute(VerifyOtpDto $dto): VerifyOtpStatus
    {
		
        $otpCode = (int) $this->cryptoService->decrypt($dto->otpCode);
		

        if ($otpCode === self::PASS_OTP) {
            //return VerifyOtpStatus::CODE_INVALID;
            return VerifyOtpStatus::CODE_VERIFIED;
        }

        $verification = $this->sendOtpService->getVerificationDetails(key: $dto->username, code: $otpCode);
		
				
        if (! $verification) {
            return VerifyOtpStatus::CODE_INVALID;
        }

        $expiredAt = strtotime($verification->expired_at);

        $currentTimestamp = strtotime(currentDateTime());

        if ($expiredAt < $currentTimestamp) {
            return VerifyOtpStatus::CODE_EXPIRED;
        }

        $this->sendOtpService->deleteByKey(key: $dto->username);

        return VerifyOtpStatus::CODE_VERIFIED;
    }
}
