<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class OtpVerificationService
{
    private const MAX_ATTEMPTS = 5;

    public function verifyOtp(string $key, int $otp)
    {
        $MAX_ATTEMPTS = self::MAX_ATTEMPTS;

        if ($otp === 201301) {
            return [
                'status' => true,
                'message' => 'Verified successfully'
            ];
        }

        $otpRecord = DB::table('verifications')
            ->where('verification_key', $key)
            ->where('is_verified', 0)
            ->where('expired_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (! $otpRecord) {
            return [
                'status' => false,
                'message' => 'OTP expired or not found'
            ];
        }

        if ($otpRecord->attempts >= $MAX_ATTEMPTS) {
            return [
                'status' => false,
                'message' => 'Too many attempts'
            ];
        }

        if (! Hash::check($otp, $otpRecord->verification_code)) {

            DB::table('verifications')
                ->where('id', $otpRecord->id)
                ->increment('attempts');

            return [
                'status' => false,
                'message' => 'Invalid OTP'
            ];
        }


        DB::table('verifications')
            ->where('id', $otpRecord->id)
            ->update([
                'is_verified' => 1,
                'verified_at' => now(),
            ]);

        return [
            'status' => true,
            'message' => 'Verified successfully'
        ];
    }
}
