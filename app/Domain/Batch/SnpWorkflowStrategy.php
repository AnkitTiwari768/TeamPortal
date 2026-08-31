<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Batch\BatchStatus;
use App\Domain\Batch\ProcessBatchHelper;
use App\Domain\Workflow\WorkflowService;
use Illuminate\Support\Facades\DB;

class SnpWorkflowStrategy implements BatchWorkflowStrategy
{
    public function __construct(
        private ProcessBatchHelper $helper,
        private WorkflowService $workflowService
    ) {}

    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void
    {
        $approved = DB::table('claims')
            ->whereIn('id', $batchClaims->pluck('claim_id')->toArray())
            ->where(function ($q) {
                $q->where('claim_status', BatchStatus::SENT_TO_SNP_FOR_INVOICE->value)
                  ->orWhere('claim_status', BatchStatus::SENT_TO_SNP->value);
            })
            ->pluck('id')
            ->toArray();

        // Upload invoice (only once)
        if ($data['action'] !== 'proceed-batch-workflow-to-ca') {
            $this->helper->transitionBatch($workflowTypeId, $batchId, 'upload_invoice');

            DB::table('dy_attachments')->insert([
                'id' => DB::raw('UUID()'),
                'entity_type' => EntityType::BATCH->value,
                'entity_id' => $batchId,
                'attachment_type_id' => $data['document_category_id'],
                'file_path' => $data['file_upload_id'],
                'uploaded_by' => authId(),
                'created_at' => now(),
            ]);
        }

        $this->helper->transitionBatch($workflowTypeId, $batchId, 'send');

        foreach ($approved as $claimId) {
            if ($data['action'] !== 'proceed-batch-workflow-to-ca') {
                $this->helper->transitionClaim($workflowTypeId, $claimId, 'upload_invoice');
            }

            $this->helper->transitionClaim($workflowTypeId, $claimId, 'send');
        }

        $this->helper->finalizeBatch($batchId, $workflowTypeId, $approved, []);
    }
}