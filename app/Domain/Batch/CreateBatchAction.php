<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Batch\BatchRepository;
use App\Domain\Batch\BatchStatus;
use App\Domain\ClaimType\ClaimTypeRepository;
use App\Domain\Workflow\WorkflowService;
use App\Utils\Calendar;
use Illuminate\Support\Facades\DB;
use App\Web\BatchWorkflow\HasTimeline;
use App\Web\Notification\SendNotificationEvent;
use App\Domain\Batch\Notify;


final readonly class CreateBatchAction
{
    use HasTimeline;
    use Notify;

    public function __construct(
        private BatchRepository $batchRepository
    ) {}

    public function execute(CreateBatchDto $dto)
    {
        return DB::transaction(function () use ($dto) {

            $claimTypeRepository = app(ClaimTypeRepository::class);

            $batchId = uuid();
            $financialYear = Calendar::getCurrentFinancialYear();
            $month = Calendar::getCurrentMonth();
            $claimTypeId = $claimTypeRepository->getClaimTypeIdBySlug($dto->claim_type_id);
            $batchNumber = BatchNumber::generate($claimTypeId);
            $initialWorkflowState = $this->batchRepository->getWorkflowInitialStateByClaimSlug($dto->claim_type_id);

            DB::table('dy_batches')->insert([
                'id'              => $batchId,
                'claim_type_id'   => $claimTypeId,
                'financial_year'  => $financialYear,
                'month'           => $month,
                'batch_number'    => $batchNumber,
                'status'          => BatchStatus::BATCH_CREATED->value,
                'current_status'  => $initialWorkflowState->state_key,
                'submitted_at'    => now(),
                'submitted_by'    => authId(),
                'created_at'      => now(),
                'created_by'      => authId(),
            ]);

            DB::table('dy_workflow_instances')->insert([
                'id' => uuid(),
                'workflow_type_id' => $initialWorkflowState->workflow_type_id,
                'entity_type' => EntityType::BATCH->value,
                'entity_id' => $batchId,
                'current_state_id' => $initialWorkflowState->id,
                'created_at' => now()
            ]);

            $workflowService = app(WorkflowService::class);

            $workflowService->transition(
                workflowTypeId: $initialWorkflowState->workflow_type_id,
                entityType: EntityType::BATCH->value,
                entityId: $batchId,
                action: 'create',
                userRole: authRoleName()
            );

            $workflowService->autoTransition(
                workflowTypeId: $initialWorkflowState->workflow_type_id,
                entityType: EntityType::BATCH->value,
                entityId: $batchId,
                userRole: authRoleName()
            );


            $batchClaims = [];
            $claimWorkflowInstances = [];

            foreach ($dto->claim_id as $claimId) {
                $batchClaims[] = [
                    'id'         => uuid(),
                    'batch_id'   => $batchId,
                    'claim_id'   => $claimId,
                    'status'     => BatchStatus::PENDING->value,
                    'created_at' => now(),
                    'created_by' => authId(),
                ];

                $claimWorkflowInstances[] = [
                    'id'                => uuid(),
                    'workflow_type_id'  => $initialWorkflowState->workflow_type_id,
                    'entity_type'       => EntityType::CLAIM->value,
                    'entity_id'         => $claimId,
                    'current_state_id'  => $initialWorkflowState->id,
                    'created_at'        => now(),
                ];
            }

            if ($batchClaims) {
                DB::table('dy_batch_claims')->insert($batchClaims);
            }

            if ($claimWorkflowInstances) {
                DB::table('dy_workflow_instances')->insert($claimWorkflowInstances);
            }

            foreach ($dto->claim_id as $claimId) {
                $workflowService->transition(
                    workflowTypeId: $initialWorkflowState->workflow_type_id,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                    action: 'create',
                    userRole: authRoleName()
                );

                $workflowService->autoTransition(
                    workflowTypeId: $initialWorkflowState->workflow_type_id,
                    entityType: EntityType::CLAIM->value,
                    entityId: $claimId,
                    userRole: authRoleName()
                );
            }

            $currentInstance = $workflowService->getWorkflowInstance(
                workflowTypeId: $initialWorkflowState->workflow_type_id,
                entityType: EntityType::BATCH->value,
                entityId: $batchId,
            );

            $workflowState = $workflowService->getWorkflowStateById($currentInstance->current_state_id);

            DB::table('dy_batches')->where('id', $batchId)->update([
                'status' => BatchStatus::getStatusByKey($workflowState->state_key),
                'current_status' => $workflowState->state_key
            ]);

            if ($dto->is_declaration_agreed) {
                DB::table('dy_declarations')->insert([
                    'id' => uuid(),
                    'entity_type' => EntityType::BATCH->value,
                    'entity_id' => $batchId,
                    'is_agreed' => $dto->is_declaration_agreed,
                    'agreed_by' => authId(),
                    'created_at' => now()
                ]);
            }

            DB::table('claims')->whereIn('id', $dto->claim_id)->update([
                'claim_status' => BatchStatus::getStatusByKey($workflowState->state_key),
            ]);

            $this->createBatchClaimWorkflowTimeline(
                batchId: $batchId,
                claims: $dto->claim_id,
                status: BatchStatus::BATCH_CREATED,
                comments: request('comments') ?? null
            );

            $this->createTimeline([
                'batch_id' => $batchId,
                'comments' => 'Batch Created',
                'status' => 'create',
                'claims' => $this->createBatchClaimTimelineDetails(
                    batchId: $batchId,
                    claimIds: $dto->claim_id,
                    status: 'submitted'
                )
            ]);

            if (
                acl('demand-generation-view')
                || acl('transport-and-logistic-view')
                || acl('claim-packaging-view')
                || acl('claim-account-view')
                || acl('packaging-claim-view')
                || acl('demand-generation-claim-view')
                || acl('catalogue-created-claim-view')
                || acl('logistic-transportation-claim-view')
                || acl('account-management-claim-view')
                || acl('claim-view')
            ) {

                $this->notify(
                    toRoleSlug: 'ondc-admin',
                    templateKey: 'batch-received-snp-np-verification',
                    type: 2,
                    message: [
                        'NP_NAME' => $this->getUserNameById(authId()),
                        'BATCH_NUMBER' => $batchNumber
                    ]
                );
            }

            return true;
        });
    }
}
