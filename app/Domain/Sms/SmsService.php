<?php

declare(strict_types=1);

namespace App\Domain\Sms;

use App\Domain\Sms\TemplateName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SmsService
{
    private const OTP_EXPIRATION_TIME = 2; // two minutes

    public static function populateTemplate(string $template, array $variables, int $expectedCount): string
    {
        // Match all placeholders like {#var#}, {#Var#}, {#VAR#}, etc.
        preg_match_all('/\{#var#\}/i', $template, $matches);

        $placeholderCount = count($matches[0]);

        if ($placeholderCount !== $expectedCount) {
            throw new \InvalidArgumentException("Mismatch: expected $expectedCount variables, found $placeholderCount placeholders.");
        }

        if (count($variables) !== $expectedCount) {
            throw new \InvalidArgumentException("Mismatch: expected $expectedCount replacement values, received " . count($variables));
        }

        // Replace placeholders in order
        $index = 0;
        $final = preg_replace_callback('/\{#var#\}/i', function () use (&$variables, &$index) {
            return $variables[$index++];
        }, $template);

        return $final;
    }

    public function sendSMS(
        string $mobile,
        int $otpCode,
        string $message,
        string $entityId,
        string $templateId
    ): bool {

        DB::table('verifications')->insert([
            'id' => uuid(),
            'verification_key' => $mobile,
            'verification_code' => Hash::make($otpCode),
            'expired_at' => now()->addMinutes(self::OTP_EXPIRATION_TIME),
            'attempts' => 0,
            'is_verified' => 0,
            'created_at' => now(),
        ]);

        return app(AirtelSmsProvider::class)->sendSMS(
            mobile: $mobile,
            message: $message,
            entityId: $entityId,
            templateId: $templateId
        );
    }

    public function getSmsTemplateDetails(TemplateName $templateName)
    {
        return DB::table('sms_templates')
            ->where('template_name', $templateName->value)
            ->where('template_status', true)
            ->first();
    }
}
