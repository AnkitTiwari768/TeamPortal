<?php

namespace App\Domain\UbpIntegration;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Carbon\Carbon;

class UbpTokenService
{
    public function generateUserToken(array $payload): string
    {
        $payload['timestamp'] = now()->toDateTimeString();
        return Crypt::encryptString(json_encode($payload));
    }

     public function decryptUserToken(string $token): ?array
    {
        try {
            $payload = Crypt::decryptString($token);
            $data = json_decode($payload, true);

            if (!is_array($data)) return null;

            if (
                empty($data['udyam_no']) ||
                empty($data['mobile']) ||
                // empty($data['email']) ||
                empty($data['timestamp'])
            ) return null;

            $tokenTime = Carbon::parse($data['timestamp']);

            if (now()->diffInMinutes($tokenTime) > 60) return null;

            return $data;

        } catch (DecryptException $e) {
            return null;
        }
    }
}
