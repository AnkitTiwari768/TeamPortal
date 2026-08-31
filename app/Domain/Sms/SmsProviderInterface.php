<?php

declare(strict_types=1);

namespace App\Domain\Sms;

interface SmsProviderInterface
{
    public function sendSMS(
        string $mobile,
        string $message,
        string $entityId,
        string $templateId
    ): bool;
}
