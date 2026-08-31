<?php

declare(strict_types=1);

namespace App\Domain\Sms;

enum TemplateName: string
{
    case MSME_REGISTRATION = 'MSME Registration';
    case SNP_REGISTRATION  = 'SNP Registration';
    case MSME_MAPPING      = 'MSME MAPPING';
    case MSME_SEND_OTP      = 'MSME SEND OTP';
    case WORKFLOW_TEMPLATE = 'Workflow Template';
}
