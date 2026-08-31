<?php 

declare(strict_types=1);

namespace App\Domain\Batch;

use Illuminate\Support\Facades\DB;

class CAWorkflowStrategy implements BatchWorkflowStrategy
{
    public function __construct(
        private ProcessBatchHelper $helper
    ) {}

    public function process(string $batchId, string $workflowTypeId, $batchClaims, array $data): void
    {
        $approved = DB::table('claims')
            ->whereIn('id', $batchClaims->pluck('claim_id')->toArray())
            ->where('claim_status', BatchStatus::SENT_TO_CA->value)
            ->pluck('id')
            ->toArray();

        // Upload certificate
        $this->helper->transitionBatch($workflowTypeId, $batchId, 'upload_certificate');

        DB::table('dy_attachments')->insert([
            'id' => DB::raw('UUID()'),
            'entity_type' => EntityType::BATCH->value,
            'entity_id' => $batchId,
            'attachment_type_id' => $data['document_category_id'],
            'file_path' => $data['file_upload_id'],
            'uploaded_by' => authId(),
            'created_at' => now(),
        ]);

        $this->helper->transitionBatch($workflowTypeId, $batchId, 'send');

        foreach ($approved as $claimId) {
            $this->helper->transitionClaim($workflowTypeId, $claimId, 'upload_certificate');
            $this->helper->transitionClaim($workflowTypeId, $claimId, 'send');
        }

        $this->helper->finalizeBatch($batchId, $workflowTypeId, $approved, []);
    }
}