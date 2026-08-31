<?php

declare(strict_types=1);

namespace App\Domain\Sms;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final readonly class AirtelSmsProvider implements SmsProviderInterface
{
    public function sendSMS(
        string $mobile,
        string $message,
        string $entityId,
        string $templateId
    ): bool {
        $config = config('sms.smartping');

        $payload = [
            'username'               => $config['username'],
            'password'               => $config['password'],
            'unicode'                => false,
            'from'                   => $config['sender'],
            'to'                     => $mobile,
            'dltContentId'           => $templateId,
            'dltPrincipalEntityId'   => $entityId,
            'text'                   => $message,
        ];

        try {
            $response = Http::timeout($config['timeout'])
                ->asForm()
                ->post($config['base_url'], $payload);

            DB::table('sms_logs')->insert([
                'sms_request' => json_encode([
                    'url' => $config['base_url'],
                    'params' => $payload
                ]),
                'sms_response' => json_encode($response->body()),
                'status' => $response->successful() ? 'sent' : 'failed',
                'logged_at' => now()
            ]);
            return $response->successful();
        } catch (\Throwable $e) {

            Log::error('SMS sending failed', [
                'provider' => 'smartping',
                'mobile'   => $mobile,
                'error'    => $e->getMessage(),
            ]);

            DB::table('sms_logs')->insert([
                'sms_request' => json_encode([
                    'url' => $config['base_url'],
                    'params' => $payload
                ]),
                'sms_response' => $e->getMessage(),
                'status' => 'error',
                'logged_at' => now()
            ]);

            return false;
        }
    }
}
