<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreComponentUtilizationMappingAction
{
    public function execute(ComponentUtilizationMappingDTO $dto, ?string $id = null): ComponentUtilizationMapping
    {
        return DB::transaction(function () use ($dto, $id) {
            $headerData = [
                'financial_year'            => $dto->financialYear,
                'duration_id'               => $dto->durationId,
                'sub_duration_id'           => $dto->subDurationId,
                'target_major_component_id' => $dto->targetMajorComponentId,
                'target_sub_component_id'   => $dto->targetSubComponentId,
                'remarks'                   => $dto->remarks,
                'status'                    => 'COMPLETED',
            ];

            if ($id) {
                // Edit mode
                $mapping = ComponentUtilizationMapping::with('details')->findOrFail($id);
                $headerData['updated_by'] = AuthId();
                $mapping->update($headerData);

                // Delete previous details
                $mapping->details()->delete();
            } else {
                // Create mode
                $headerData['id'] = (string) Str::uuid();
                $headerData['created_by'] = AuthId();
                $mapping = ComponentUtilizationMapping::create($headerData);
            }

            $totalMaxUtilizationAmount = 0.00;

            foreach ($dto->eligibleLines as $line) {
                $maxAmount = (float) ($line['max_utilization_amount'] ?? 0);
                if ($maxAmount < 0) {
                    continue;
                }

                $totalMaxUtilizationAmount += $maxAmount;

                $mapping->details()->create([
                    'id'                          => (string) Str::uuid(),
                    'eligible_major_component_id' => $line['eligible_major_component_id'],
                    'eligible_sub_component_id'   => $line['eligible_sub_component_id'] ?? null,
                    'total_allocated_amount'      => (float) ($line['total_allocated_amount'] ?? 0),
                    'total_distributed_amount'    => (float) ($line['total_distributed_amount'] ?? 0),
                    'max_utilization_amount'      => $maxAmount,
                    'remarks'                     => $line['remarks'] ?? null,
                ]);
            }

            $mapping->update(['total_max_utilization_amount' => $totalMaxUtilizationAmount]);

            return $mapping->load('details');
        });
    }
}
