<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Illuminate\Support\Facades\DB;

class ComponentUtilizationMappingService
{
    /**
     * Get allocated and distributed balances for components in a financial year.
     */
    public function getComponentBalances(string $financialYear, ?string $majorComponentId = null, ?string $subComponentId = null, ?string $durationId = null, ?string $subDurationId = null)
    {
        $query = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as mc', 'fp.major_component_id', '=', 'mc.id')
            ->leftJoin('attribute_values as sc', 'fp.sub_component_id', '=', 'sc.id')
            ->where('fp.financial_year', $financialYear);

        if ($majorComponentId) {
            $query->where('fp.major_component_id', $majorComponentId);
        }

        if ($subComponentId) {
            $query->where('fp.sub_component_id', $subComponentId);
        }

        if ($durationId) {
            $query->where('fp.duration_id', $durationId);
        }

        if ($subDurationId) {
            $query->where('fp.sub_duration_id', $subDurationId);
        }

        $pools = $query->select(
            'fp.major_component_id',
            'mc.attribute_value as major_component_name',
            'fp.sub_component_id',
            'sc.attribute_value as sub_component_name',
            DB::raw('SUM(fp.total_allocated_amount) as total_allocated_amount'),
            DB::raw('SUM(fp.total_distributed_amount) as total_distributed_amount'),
            DB::raw('SUM(fp.remaining_balance) as remaining_balance')
        )
        ->groupBy('fp.major_component_id', 'fp.sub_component_id', 'mc.attribute_value', 'sc.attribute_value')
        ->get();

        return $pools;
    }

    /**
     * Get Component Utilization Mapping details for edit page.
     */
    public function getMappingEditDetails(string $id): array
    {
        $mapping = ComponentUtilizationMapping::with('details')->findOrFail($id);

        $detailsFormatted = $mapping->details->map(function ($detail) {
            return [
                'id'                          => $detail->id,
                'eligible_major_component_id' => $detail->eligible_major_component_id,
                'eligible_sub_component_id'   => $detail->eligible_sub_component_id,
                'total_allocated_amount'      => $detail->total_allocated_amount,
                'total_distributed_amount'    => $detail->total_distributed_amount,
                'max_utilization_amount'      => $detail->max_utilization_amount,
                'remarks'                     => $detail->remarks,
            ];
        })->values()->toArray();

        return [
            'mapping' => $mapping->toArray(),
            'details' => $detailsFormatted,
        ];
    }

    /**
     * Get Component Utilization Mapping details for view page.
     */
    public function getMappingViewDetails(string $id): array
    {
        $mapping = ComponentUtilizationMapping::with('details')->findOrFail($id);

        // Collect attribute IDs for name resolution
        $attributeIds = collect([
            $mapping->duration_id,
            $mapping->sub_duration_id,
            $mapping->target_major_component_id,
            $mapping->target_sub_component_id,
        ])
            ->merge($mapping->details->pluck('eligible_major_component_id'))
            ->merge($mapping->details->pluck('eligible_sub_component_id'))
            ->filter()
            ->unique();

        $attributeValues = DB::table('attribute_values')
            ->whereIn('id', $attributeIds)
            ->pluck('attribute_value', 'id');

        $detailsFormatted = $mapping->details->map(function ($detail) use ($attributeValues) {
            return [
                'id'                           => $detail->id,
                'eligible_major_component_id'   => $detail->eligible_major_component_id,
                'eligible_major_component_name' => $attributeValues[$detail->eligible_major_component_id] ?? '-',
                'eligible_sub_component_id'     => $detail->eligible_sub_component_id,
                'eligible_sub_component_name'   => $attributeValues[$detail->eligible_sub_component_id] ?? '-',
                'total_allocated_amount'        => $detail->total_allocated_amount,
                'total_distributed_amount'      => $detail->total_distributed_amount,
                'max_utilization_amount'        => $detail->max_utilization_amount,
                'remarks'                       => $detail->remarks,
            ];
        });

        return [
            'mapping' => array_merge(
                $mapping->toArray(),
                [
                    'duration_name'               => $attributeValues[$mapping->duration_id] ?? '-',
                    'sub_duration_name'           => $attributeValues[$mapping->sub_duration_id] ?? '-',
                    'target_major_component_name' => $attributeValues[$mapping->target_major_component_id] ?? '-',
                    'target_sub_component_name'   => $attributeValues[$mapping->target_sub_component_id] ?? '-',
                ]
            ),
            'details' => $detailsFormatted,
        ];
    }
}
