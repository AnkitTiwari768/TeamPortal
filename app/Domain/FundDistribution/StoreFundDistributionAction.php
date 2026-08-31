<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Web\Allocation\PoolService;
use App\Traits\HasFileUpload;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreFundDistributionAction
{
    use HasFileUpload;

    public function execute(FundDistributionDTO $dto, ?string $id = null): FundDistribution
    {
        return DB::transaction(function () use ($dto, $id) {
            $poolService = app(PoolService::class);

            // 1. Resolve Pool Key
            $keyData = [
                'financial_year'    => $dto->financialYear,
                'duration_id'       => $dto->durationId,
                'sub_duration_id'   => $dto->subDurationId,
                'major_component_id'=> $dto->majorComponentId,
                'sub_component_id'  => $dto->subComponentId,
            ];

            $key = $poolService->resolvePoolKey($keyData);
            
            // Acquire Row Lock or lookup pool
            $pool = $poolService->findPool($key, true);

            if (!$pool || (float)$pool->total_allocated_amount <= 0) {
                throw new \Exception("Operational Error: No allocation pool or component utilization discovered for these dimensions. Please create an allocation first.");
            }

            // Normalization
            $distAmount = $dto->distributionAmount;
            $tdsPercent = $dto->tdsPercentage;
            $tdsAmount = ($distAmount * $tdsPercent) / 100;
            $netPayable = $distAmount - $tdsAmount;

            // Check projected remaining balance. Component Utilization Mapping transfers
            // are already folded into total_allocated_amount at the time they're created
            // (see ComponentUtilizationMappingService::storeMapping), so no separate
            // "utilization bonus" needs to be added here.
            $currentAllocated = (float)$pool->total_allocated_amount;
            $currentDistributed = (float)$pool->total_distributed_amount;

            $recordData = [
                'financial_year'      => $dto->financialYear,
                'duration_id'         => $dto->durationId,
                'sub_duration_id'     => $dto->subDurationId,
                'major_component_id'  => $dto->majorComponentId,
                'sub_component_id'    => $dto->subComponentId,
                'fund_pool_id'        => $pool->id,
                
                'distribution_amount' => $distAmount,
                'tds_percentage'      => $tdsPercent,
                'tds_amount'          => $tdsAmount,
                'net_payable_amount'  => $netPayable,
                
                'sanction_order_number' => $dto->sanctionOrderNumber,
                'sanction_order_date'   => !empty($dto->sanctionOrderDate) ? $this->parseDate($dto->sanctionOrderDate) : null,
                'remarks'               => $dto->remarks,
                
                'upload_document'               => $dto->uploadDocument,
                'upload_document_original_name' => isset($dto->uploadDocument) ? self::originalName($dto->uploadDocument) : null,
            ];

            if ($id) {
                // Edit mode
                $fundDistribution = FundDistribution::findOrFail($id);

                $oldDistAmount = (float)$fundDistribution->distribution_amount;
                $projectedRemaining = $currentAllocated - ($currentDistributed - $oldDistAmount + $distAmount);
                if ($projectedRemaining < 0) {
                    $available = $currentAllocated - ($currentDistributed - $oldDistAmount);
                    throw new \Exception("Validation Error: Insufficient funds in allocation pool. Remaining balance cannot be negative (requested: ₹{$distAmount}, available: ₹{$available}).");
                }

                // --- REVERSE EXISTING IMPACT FROM OLD POOL ---
                $oldKey = $poolService->resolvePoolKey([
                    'financial_year'     => $fundDistribution->financial_year,
                    'duration_id'        => $fundDistribution->duration_id,
                    'sub_duration_id'    => $fundDistribution->sub_duration_id,
                    'major_component_id' => $fundDistribution->major_component_id,
                    'sub_component_id'   => $fundDistribution->sub_component_id,
                ]);
                $poolService->updatePoolDistribution($oldKey, -$oldDistAmount);

                // --- UPDATE RECORD ---
                $recordData['updated_by'] = AuthId();
                $fundDistribution->update($recordData);

                // --- APPLY NEW IMPACT ---
                $poolService->updatePoolDistribution($key, $distAmount);
            } else {
                // Create mode
                $projectedRemaining = $currentAllocated - ($currentDistributed + $distAmount);
                if ($projectedRemaining < 0) {
                    $available = $currentAllocated - $currentDistributed;
                    throw new \Exception("Validation Error: Insufficient funds in allocation pool. Remaining balance cannot be negative (requested: ₹{$distAmount}, available: ₹{$available}).");
                }

                $recordData['id'] = (string) Str::uuid();
                $recordData['source_type'] = 'MANUAL';
                $recordData['created_by'] = AuthId();
                $recordData['updated_by'] = AuthId();

                $fundDistribution = FundDistribution::create($recordData);

                // --- APPLY NEW IMPACT ---
                $poolService->updatePoolDistribution($key, $distAmount);
            }

            return $fundDistribution;
        });
    }

    private function parseDate(string $date): string
    {
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
        return Carbon::parse($date)->format('Y-m-d');
    }
}
