<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Traits\Respond;

final readonly class BatchClaimController
{
    use Respond;

    public function index(BatchClaimListQuery $query, string $batchId, string $status)
    {
        return $this->success(data: $query->execute($batchId, $status));
    }
}
