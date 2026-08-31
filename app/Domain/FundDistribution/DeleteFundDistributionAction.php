<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Web\Allocation\PoolService;
use Illuminate\Support\Facades\DB;

class DeleteFundDistributionAction
{
    public function execute(string $id): bool
    {
        return DB::transaction(function () use ($id) {
            $fundDistribution = FundDistribution::findOrFail($id);

            // 1. Resolve Pool Key using existing record dimensions
            $keyData = [
                'financial_year'    => $fundDistribution->financial_year,
                'duration_id'       => $fundDistribution->duration_id,
                'sub_duration_id'   => $fundDistribution->sub_duration_id,
                'major_component_id'=> $fundDistribution->major_component_id,
                'sub_component_id'  => $fundDistribution->sub_component_id,
            ];

            $poolService = app(PoolService::class);
            $key = $poolService->resolvePoolKey($keyData);

            // 2. Back out distribution amount from the Pool
            $poolService->updatePoolDistribution($key, -((float)$fundDistribution->distribution_amount));

            // 3. Set updater details and delete
            $fundDistribution->updated_by = AuthId();
            $fundDistribution->save();

            return (bool) $fundDistribution->delete();
        });
    }
}
