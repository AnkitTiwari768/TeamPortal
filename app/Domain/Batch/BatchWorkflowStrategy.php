<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

interface BatchWorkflowStrategy
{
    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void;
}