<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListFundDistributionAction
{
    use DataTable;

    protected array $columns = [
        1 => 'fp.financial_year',
        2 => 'mc.attribute_value',
        3 => 'sc.attribute_value',
        4 => 'total_allocated',
        5 => 'total_distributed',
        6 => 'remaining'
    ];

    public function execute(): array
    {
        [$limit, $order, $dir, $search, $page, $filters, $start] = $this->getDataTableParams(null, $this->columns);

        $query = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as mc', DB::raw('CONVERT(mc.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.major_component_id')
            ->leftJoin('attribute_values as sc', DB::raw('CONVERT(sc.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.sub_component_id')
            ->select(
                'fp.financial_year',
                'fp.major_component_id',
                'fp.sub_component_id',
                'mc.attribute_value as major_component_name',
                'sc.attribute_value as sub_component_name',
                DB::raw('COALESCE(SUM(fp.total_allocated_amount), 0) as total_allocated'),
                DB::raw('COALESCE(SUM(fp.total_distributed_amount), 0) as total_distributed'),
                DB::raw('COALESCE(SUM(fp.remaining_balance), 0) as remaining')
            )
            ->groupBy(
                'fp.financial_year',
                'fp.major_component_id',
                'fp.sub_component_id',
                'mc.attribute_value',
                'sc.attribute_value'
            )
            ->havingRaw('total_distributed > 0');

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

        if (in_array($orderCol, ['total_allocated', 'total_distributed', 'remaining'])) {
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
