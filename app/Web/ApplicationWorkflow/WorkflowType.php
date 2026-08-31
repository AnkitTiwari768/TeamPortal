<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

enum WorkflowType: string
{
    case CLAIM_CATALOGUE = 'claim-for-catalogue-creation';

    case SNP_REGISTRATION = 'snp-registration';
}
