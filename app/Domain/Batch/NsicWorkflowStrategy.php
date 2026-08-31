<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Batch\BatchRepository;
use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\ProcessBatchHelper;

class NsicWorkflowStrategy implements BatchWorkflowStrategy
{
    public function __construct(
        private BatchRepository $batchRepository,
        private ProcessBatchHelper $helper
    ) {}

    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void
    {
        if ($data['action'] === 'proceed-batch-workflow-sent-to-finance') {
            $pending = 0;
            $approved = $this->helper->getApprovedClaims(
                $batchClaims,
                BatchStatus::SENT_TO_NSIC_BY_SNP->value
            );
            $rejected = [];
        } else {
            $pending = $this->batchRepository->getPendingClaimsForNsic($batchId);

            if ($pending > 0) {
                throw new \Exception("Pending claims found...");
            }

            $approved = $this->helper->getApprovedClaims(
                $batchClaims,
                BatchStatus::APPROVED_BY_NSIC->value
            );

            $rejected = $this->helper->getApprovedClaims(
                $batchClaims,
                BatchStatus::REJECTED_BY_NSIC->value
            );
        }

        $this->helper->handleTransitions($workflowTypeId, $batchId, $approved, $rejected, $data);

        $this->helper->finalizeBatch($batchId, $workflowTypeId, $approved, $rejected);
    }
}