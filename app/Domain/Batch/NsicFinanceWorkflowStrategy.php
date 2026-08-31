<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Batch\BatchRepository;
use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\ProcessBatchHelper;
use Illuminate\Support\Facades\DB;

class NsicFinanceWorkflowStrategy implements BatchWorkflowStrategy
{
    public function __construct(
        private BatchRepository $batchRepository,
        private ProcessBatchHelper $helper
    ) {}

    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void
    {
        $pending = $this->batchRepository->getPendingClaimsForNSICFinance($batchId);

        if ($pending > 0) {
            throw new \Exception("Pending claims found...");
        }

        $approved = $this->helper->getApprovedClaims(
            $batchClaims,
            BatchStatus::APPROVED->value
        );

        $rejected = $this->helper->getApprovedClaims(
            $batchClaims,
            BatchStatus::REJECTED_NSIC_FINANCE->value
        );

        $this->helper->handleTransitions($workflowTypeId, $batchId, $approved, $rejected, $data);

        $this->helper->finalizeBatch($batchId, $workflowTypeId, $approved, $rejected);

        // 🔥 Extra business logic (unique to finance)
        if (!empty($approved)) {
            $teamIds = DB::table('claims')
                ->whereIn('id', $approved)
                ->pluck('team_registration_id')
                ->toArray();

            DB::table('team_msme_schemes')
                ->whereIn('team_id', $teamIds)
                ->update([
                    'is_catalogue_claim_approved' => true
                ]);
        }
    }
}