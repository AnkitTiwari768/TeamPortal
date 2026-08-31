<?php

declare(strict_types=1);

namespace App\Domain\Batch;

use App\Domain\Workflow\WorkflowService;
use Illuminate\Support\Facades\DB;

class BatchPaymentAction
{
    use Notify;

    public function execute(array $data)
    {
        DB::transaction(function () use ($data) {
            $batchId = $data['batch_id'];
            $batchRepository = app(BatchRepository::class);
            $pendingClaims = -1;

            $workflowService = app(WorkflowService::class);
            $workflowTypeId = $workflowService->getWorkflowTypeIdBySlug($data['claim_type']);

            $batchClaims = DB::table('dy_batch_claims')->where('batch_id', $batchId)->get();

            $approvedClaims = $rejectedClaims = [];

            if (hasRole('nsic-finance') || hasRole('nsic-checker')) {
                if (hasRole('nsic-checker')) {
                    $pendingClaims = $batchRepository->getPendingClaimsForNsicChecker($batchId);
                } else {
                    $pendingClaims = $batchRepository->getPendingClaimsForNSICFinance($batchId);
                }

                $approvedClaims = DB::table('claims')
                    ->whereIn('id', $batchClaims->pluck('claim_id')->toArray())
                    ->where('claim_status', BatchStatus::APPROVED->value)
                    ->pluck('id')
                    ->toArray();
            }

            if ($pendingClaims > 0) {
                throw new \Exception("Pending claims found...");
            }

            if (hasRole('nsic-finance') || hasRole('nsic-checker')) {
                $workflowService->transition(
                    workflowTypeId: $workflowTypeId,
                    entityType: EntityType::BATCH->value,
                    entityId: $batchId,
                    action: 'payment_completed',
                    userRole: authRoleName()
                );
            }

            foreach ($approvedClaims as $claimId) {
                if (hasRole('nsic-finance') || hasRole('nsic-checker')) {
                    $workflowService->transition(
                        workflowTypeId: $workflowTypeId,
                        entityType: EntityType::CLAIM->value,
                        entityId: $claimId,
                        action: 'payment_completed',
                        userRole: authRoleName()
                    );
                }
            }

            $currentInstance = $workflowService->getWorkflowInstance(
                workflowTypeId: $workflowTypeId,
                entityType: EntityType::BATCH->value,
                entityId: $batchId,
            );

            $workflowState = $workflowService->getWorkflowStateById($currentInstance->current_state_id);

            if (!\Illuminate\Support\Facades\Schema::hasColumn('dy_batches', 'sanction_order_number')) {
                \Illuminate\Support\Facades\Schema::table('dy_batches', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->string('sanction_order_number', 255)->nullable()->after('pfms_number');
                });
            }
            if (!\Illuminate\Support\Facades\Schema::hasColumn('dy_batches', 'sanction_order_date')) {
                \Illuminate\Support\Facades\Schema::table('dy_batches', function (\Illuminate\Database\Schema\Blueprint $table) {
                    $table->date('sanction_order_date')->nullable()->after('sanction_order_number');
                });
            }

            DB::table('dy_batches')->where('id', $batchId)->update([
                'status' => BatchStatus::getStatusByKey($workflowState->state_key),
                'current_status' => $workflowState->state_key,
                'pfms_number' => $data['pfms_number'],
                'sanction_order_number' => $data['sanction_order_number'] ?? null,
                'sanction_order_date' => $data['sanction_order_date'] ?? null,
                'updated_at' => now()
            ]);

            if ($approvedClaims) {
                DB::table('dy_batch_claims')
                    ->where('batch_id', $batchId)
                    ->whereIn('claim_id', $approvedClaims)
                    ->update([
                        'status' => BatchStatus::getStatusByKey($workflowState->state_key),
                        'updated_at' => now()
                    ]);

                DB::table('claims')
                    ->whereIn('id', $approvedClaims)
                    ->update([
                        'claim_status' => BatchStatus::getStatusByKey($workflowState->state_key),
                    ]);
            }

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

                app(\App\Domain\Batch\ClaimDistribution::class)->distribute($batchId);


                // $batch = DB::table('dy_batches')->where('id', $batchId)->first();

                // if ($batch) {  // ✅ FIXED: Changed from $batchRecord to $batch
                //     // 2. Resolve exact slug mapped to this Claim Type ID
                //     $claimSlug = DB::table('claim_types')
                //         ->where('id', $batch->claim_type_id)
                //         ->value('slug');

                //     // 2b. RESOLVE DURATION & SUB-DURATION FROM BATCH MONTH
                //     $durationId = null;
                //     $subDurationId = null;

                //     if (!empty($batch->month)) {
                //         // Fetch root duration value for 'Monthly'
                //         $monthlyDuration = DB::table('attribute_values as av')
                //             ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
                //             ->where('a.code', config('allocation.duration_code', 'duration'))
                //             ->where('av.attribute_value', 'Monthly')
                //             ->select('av.id')
                //             ->first();

                //         if ($monthlyDuration) {
                //             $durationId = $monthlyDuration->id;
                //             // Match correct month child based on numeric sort index
                //             $matchingMonth = DB::table('attribute_values')
                //                 ->where('parent_id', $durationId)
                //                 ->where('sort_order', (int) $batch->month)
                //                 ->select('id')
                //                 ->first();

                //             if ($matchingMonth) {
                //                 $subDurationId = $matchingMonth->id;
                //             }
                //         }
                //     }

                //     // 3. Hydrate real payout data for targeted claims
                //     $hydratedClaims = DB::table('claims')->whereIn('id', $approvedClaims)->get();

                //     // 4. Resolve Distribution Service Orchestrator
                //     $distService = app(\App\Web\FundDistribution\ClaimDistributionService::class);

                //     foreach ($hydratedClaims as $claim) {
                //         // Execute transactional atomic deduction
                //         $distService->createDistributionForClaim([
                //             'source_id'       => $claim->id,
                //             'source_type'     => 'CLAIM',
                //             'claim_type_slug' => $claimSlug,
                //             'financial_year'  => $batch->financial_year,
                //             'duration_id'     => $durationId,
                //             'sub_duration_id' => $subDurationId,
                //             'amount'          => (float) ($claim->total_claimed_amount ?? 0.0),
                //             'claim_number'    => $claim->application_number ?? 'N/A',
                //             'remarks'         => "Batch Payment completion: #{$batch->batch_number}"
                //         ]);
                //     }

                //     $this->sendPaymentCompletedNotification($batch);
                // }
            }
        });
    }
}
