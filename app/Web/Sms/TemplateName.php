<?php

declare(strict_types=1);

namespace App\Web\Sms;

enum TemplateName: string
{
    case MSME_REGISTRATION = 'MSME Registration';
    case SNP_REGISTRATION  = 'SNP Registration';
    case MSME_MAPPING      = 'MSME MAPPING';
    case WORKFLOW_TEMPLATE = 'Workflow Template';
}
