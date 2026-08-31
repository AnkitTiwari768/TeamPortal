<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Batch\BatchRepository;
use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\ProcessBatchHelper;

class OndcWorkflowStrategy implements BatchWorkflowStrategy
{
    public function __construct(
        private BatchRepository $batchRepository,
        private ProcessBatchHelper $helper
    ) {}

    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void
    {
        $pending = $this->batchRepository->getPendingClaimsForOndc($batchId);

        if ($pending > 0) {
            throw new \Exception("Pending claims found...");
        }

        $approved = $this->helper->getApprovedClaims($batchClaims, BatchStatus::APPROVED_BY_ONDC->value);
        $rejected = $this->helper->getApprovedClaims($batchClaims, BatchStatus::REJECTED_BY_ONDC->value);

        $this->helper->handleTransitions($workflowTypeId, $batchId, $approved, $rejected, $data);

        $this->helper->finalizeBatch($batchId, $workflowTypeId, $approved, $rejected);
    }
}