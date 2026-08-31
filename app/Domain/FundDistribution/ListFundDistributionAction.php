<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListFundDistributionAction
{
    use DataTable;

    // Indices match the datatable's visible column positions (0-based, including the
    // two non-orderable Duration/Sub Duration columns at 4-5) -- keep in sync with the
    // `columns` array in resources/views/fund_flow/fund_distribution/index.blade.php.
    protected array $columns = [
        1 => 'fp.financial_year',
        2 => 'mc.attribute_value',
        3 => 'sc.attribute_value',
        6 => 'total_allocated',
        7 => 'total_distributed',
        8 => 'remaining',
        9 => 'allocated_to_another_component'
    ];

    public function execute(): array
    {
        [$limit, $order, $dir, $search, $page, $filters, $start] = $this->getDataTableParams(null, $this->columns);

        // fund_pools.total_allocated_amount is NET of any Component Utilization Mapping
        // transfers (it's debited on the source component and credited on the target when
        // a transfer happens, so the pool's remaining_balance stays correct -- see
        // ComponentUtilizationMappingService::storeMapping()). But the "Allocation" figure
        // shown here should be the GROSS amount ever allocated/received by this component,
        // unaffected by later transferring some of it elsewhere -- that reduction belongs
        // in "Remaining", not "Allocation". Add back whatever this component has
        // transferred away so "Allocation" doesn't visibly shrink when money moves out.
        $transferredAwaySubquery = "(SELECT COALESCE(SUM(cumd.amount_to_be_allocated), 0)
            FROM component_utilization_mapping_details cumd
            INNER JOIN component_utilization_mappings cum ON cum.id COLLATE utf8mb4_unicode_ci = cumd.mapping_id COLLATE utf8mb4_unicode_ci
            WHERE cum.financial_year COLLATE utf8mb4_unicode_ci = fp.financial_year COLLATE utf8mb4_unicode_ci
            AND cumd.source_major_component = fp.major_component_id
            AND (cumd.source_sub_component = fp.sub_component_id OR (cumd.source_sub_component IS NULL AND fp.sub_component_id IS NULL)))";

        // fund_pools.duration_id/sub_duration_id are not meaningful under merged pooling
        // mode (collapsed to a fixed dummy id, see PoolService::resolvePoolKey()), so the
        // Duration/Sub Duration shown here are pulled from the actual fund_distributions
        // transactions rolled into this component's totals instead.
        $durationNamesSubquery = "(SELECT GROUP_CONCAT(DISTINCT d.attribute_value ORDER BY d.attribute_value SEPARATOR ', ')
            FROM fund_distributions fd2
            LEFT JOIN attribute_values d ON CONVERT(d.id USING utf8mb4) COLLATE utf8mb4_unicode_ci = fd2.duration_id
            WHERE fd2.deleted_at IS NULL
            AND fd2.financial_year COLLATE utf8mb4_unicode_ci = fp.financial_year COLLATE utf8mb4_unicode_ci
            AND fd2.major_component_id = fp.major_component_id
            AND (fd2.sub_component_id = fp.sub_component_id OR (fd2.sub_component_id IS NULL AND fp.sub_component_id IS NULL)))";

        $subDurationNamesSubquery = "(SELECT GROUP_CONCAT(DISTINCT sd.attribute_value ORDER BY sd.attribute_value SEPARATOR ', ')
            FROM fund_distributions fd3
            LEFT JOIN attribute_values sd ON CONVERT(sd.id USING utf8mb4) COLLATE utf8mb4_unicode_ci = fd3.sub_duration_id
            WHERE fd3.deleted_at IS NULL
            AND fd3.financial_year COLLATE utf8mb4_unicode_ci = fp.financial_year COLLATE utf8mb4_unicode_ci
            AND fd3.major_component_id = fp.major_component_id
            AND (fd3.sub_component_id = fp.sub_component_id OR (fd3.sub_component_id IS NULL AND fp.sub_component_id IS NULL))
            AND fd3.sub_duration_id IS NOT NULL)";

        $query = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as mc', DB::raw('CONVERT(mc.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.major_component_id')
            ->leftJoin('attribute_values as sc', DB::raw('CONVERT(sc.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.sub_component_id')
            ->select(
                'fp.financial_year',
                'fp.major_component_id',
                'fp.sub_component_id',
                'mc.attribute_value as major_component_name',
                'sc.attribute_value as sub_component_name',
                DB::raw("(COALESCE(SUM(fp.total_allocated_amount), 0) + $transferredAwaySubquery) as total_allocated"),
                DB::raw('COALESCE(SUM(fp.total_distributed_amount), 0) as total_distributed'),
                DB::raw('COALESCE(SUM(fp.remaining_balance), 0) as remaining'),
                DB::raw("$transferredAwaySubquery as allocated_to_another_component"),
                DB::raw("$durationNamesSubquery as duration_name"),
                DB::raw("$subDurationNamesSubquery as sub_duration_name")
            )
            ->groupBy(
                'fp.financial_year',
                'fp.major_component_id',
                'fp.sub_component_id',
                'mc.attribute_value',
                'sc.attribute_value'
            )
            ->havingRaw('total_allocated != 0 OR total_distributed != 0');

        // Contextual Filtering
        if (!empty($filters['financial_year'])) {
            $query->where('fp.financial_year', $filters['financial_year']);
        }
        if (!empty($filters['major_component_id'])) {
            $query->where('fp.major_component_id', $filters['major_component_id']);
        }
        if (!empty($filters['sub_component_id'])) {
            $query->where('fp.sub_component_id', $filters['sub_component_id']);
        }

        // Quick Search Engine
        if (!empty($search)) {
            $s = trim(str_replace(',', '', $search));
            $query->havingRaw("(financial_year LIKE ? OR major_component_name LIKE ? OR sub_component_name LIKE ?)", ["%{$s}%", "%{$s}%", "%{$s}%"]);
        }

        // Dynamic Sorting Handler
        $orderCol = $order;
        $direction = $dir ?: 'desc';

        if ($orderCol === 'id') {
            $orderCol = 'fp.financial_year';
        }

        if (in_array($orderCol, ['total_allocated', 'total_distributed', 'remaining', 'allocated_to_another_component'])) {
             $query->orderByRaw("{$orderCol} {$direction}");
        } else {
             $query->orderBy($orderCol, $direction);
        }

        // Server-side pagination count
        $totalRows = DB::table(DB::raw("({$query->toSql()}) as sub"))
                       ->setBindings($query->getBindings())
                       ->count();

        $results = $query->offset((int)$start)
                         ->limit($limit > 0 ? $limit : 10)
                         ->get();

        return [
            'draw'            => intval(request('draw', 1)),
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => $totalRows,
            'data'            => $results
        ];
    }
}
