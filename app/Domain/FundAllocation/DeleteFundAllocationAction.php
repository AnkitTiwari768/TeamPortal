<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use App\Web\Allocation\PoolService;
use Illuminate\Support\Facades\DB;

class DeleteFundAllocationAction
{
    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $fundAllocation = FundAllocation::with('componentMappings')->findOrFail($id);
            $poolService = app(PoolService::class);

            foreach ($fundAllocation->componentMappings as $mapping) {
                $key = $poolService->resolvePoolKey([
                    'financial_year'     => $fundAllocation->financial_year,
                    'duration_id'        => $fundAllocation->duration_id,
                    'sub_duration_id'    => $fundAllocation->sub_duration_id,
                    'major_component_id' => $mapping->major_component_id,
                    'sub_component_id'   => $mapping->sub_component_id,
                ]);

                $pool = $poolService->findPool($key, true);
                if ($pool && (float) $pool->total_distributed_amount > 0) {
                    throw new \RuntimeException(
                        "Cannot delete this allocation. Distribution already exists against one of its components."
                    );
                }

                $outgoingAmount = $poolService->getComponentUtilizationOutgoingAmount(
                    $fundAllocation->financial_year,
                    $fundAllocation->duration_id,
                    $fundAllocation->sub_duration_id,
                    $mapping->major_component_id,
                    $mapping->sub_component_id
                );

                if ($outgoingAmount > 0) {
                    throw new \RuntimeException(
                        "Cannot delete this allocation. Utilization has been done from one component to another component."
                    );
                }
            }

            foreach ($fundAllocation->componentMappings as $mapping) {
                $key = $poolService->resolvePoolKey([
                    'financial_year'     => $fundAllocation->financial_year,
                    'duration_id'        => $fundAllocation->duration_id,
                    'sub_duration_id'    => $fundAllocation->sub_duration_id,
                    'major_component_id' => $mapping->major_component_id,
                    'sub_component_id'   => $mapping->sub_component_id,
                ]);

                $poolService->updatePoolAllocation($key, -((float) $mapping->amount));
            }

            $fundAllocation->componentMappings()->delete();

            return (bool) $fundAllocation->delete();
        });
    }
}
