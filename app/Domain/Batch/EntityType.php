<?php

declare(strict_types=1);

namespace App\Domain\Batch;

enum EntityType: string
{
    case BATCH = 'batch';
    case CLAIM = 'claim';
}
