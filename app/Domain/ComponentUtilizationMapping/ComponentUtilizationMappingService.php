<?php

declare(strict_types=1);

namespace App\Domain\ComponentUtilizationMapping;

use App\Web\Allocation\PoolService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ComponentUtilizationMappingService
{
    public function storeMapping(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Find or create the master record for this FY/Duration/SubDuration
            $mapping = ComponentUtilizationMapping::firstOrNew(
                [
                    'financial_year' => $data['financial_year'],
                    'duration' => $data['duration'] ?? null,
                    'sub_duration' => $data['sub_duration'] ?? null,
                ]
            );

            if (!$mapping->exists) {
                $mapping->id = (string) Str::uuid();
                $mapping->status = 'Active';
                $mapping->created_by = auth()->id();
            }
            $mapping->updated_by = auth()->id();
            $mapping->save();

            // Create the detail record
            $detail = new ComponentUtilizationMappingDetail([
                'id' => (string) Str::uuid(),
                'source_major_component' => $data['source_major_component'],
                'source_sub_component' => $data['source_sub_component'],
                'allocated_amount' => $data['allocated_amount'],
                'released_amount' => $data['released_amount'],
                'remaining_balance' => $data['remaining_balance'],
                'amount_to_be_allocated' => $data['amount_to_be_allocated'],
                'target_category' => $data['target_category'],
                'target_sub_component' => $data['target_sub_component'],
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Active',
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $mapping->details()->save($detail);

            // Move the transferred amount permanently between the two components'
            // fund pools, so remaining balance, carry-forward, and distribution all
            // see this transfer through the single fund_pools source of truth
            // instead of a live-recomputed "bonus" that nothing ever consumes.
            $poolService = app(PoolService::class);
            $amount = (float) $data['amount_to_be_allocated'];

            $sourceKey = $poolService->resolvePoolKey([
                'financial_year' => $data['financial_year'],
                'duration_id' => $data['duration'] ?? null,
                'sub_duration_id' => $data['sub_duration'] ?? null,
                'major_component_id' => $data['source_major_component'],
                'sub_component_id' => $data['source_sub_component'] ?? null,
            ]);
            $poolService->updatePoolTransferOut($sourceKey, $amount);

            $targetKey = $poolService->resolvePoolKey([
                'financial_year' => $data['financial_year'],
                'duration_id' => $data['duration'] ?? null,
                'sub_duration_id' => $data['sub_duration'] ?? null,
                'major_component_id' => $data['target_category'],
                'sub_component_id' => $data['target_sub_component'] ?? null,
            ]);
            $poolService->updatePoolTransferIn($targetKey, $amount);

            return $detail;
        });
    }

    public function getMappingHistory()
    {
        return ComponentUtilizationMappingDetail::with('mapping')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
