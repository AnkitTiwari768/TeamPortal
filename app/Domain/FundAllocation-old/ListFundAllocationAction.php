<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListFundAllocationAction
{
    use DataTable;

    protected array $columns = [
        1 => 'fa.financial_year',
        2 => 'duration_av.attribute_value',
        3 => 'sub_duration_av.attribute_value',
        4 => 'fa.sanction_order_number',
        5 => 'fa.sanction_order_date',
        6 => 'fa.total_amount_allocated',
        7 => 'fa.total_available_amount',
        8 => 'fa.created_at',
    ];

    /**
     * Execute the list fund allocation action.
     *
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $mode = (int) config('allocation.pooling_mode', 1);
        $mergedPoolDurationUuid = config('allocation.merged_pool_duration_uuid', '00000000-0000-0000-0000-000000000000');
        
        $remainingAmountSubquery = $mode === 2 
            ? "(SELECT COALESCE(SUM(fp.remaining_balance), 0) FROM fund_pools fp INNER JOIN fund_allocation_component_mappings facm2 ON facm2.major_component_id = fp.major_component_id AND (facm2.sub_component_id = fp.sub_component_id OR (facm2.sub_component_id IS NULL AND fp.sub_component_id IS NULL)) WHERE facm2.fund_allocation_id = fa.id AND fp.financial_year COLLATE utf8mb4_unicode_ci = fa.financial_year COLLATE utf8mb4_unicode_ci AND fp.duration_id COLLATE utf8mb4_unicode_ci = '{$mergedPoolDurationUuid}' COLLATE utf8mb4_unicode_ci)"
            : "(SELECT COALESCE(SUM(fp.remaining_balance), 0) FROM fund_pools fp INNER JOIN fund_allocation_component_mappings facm2 ON facm2.major_component_id = fp.major_component_id AND (facm2.sub_component_id = fp.sub_component_id OR (facm2.sub_component_id IS NULL AND fp.sub_component_id IS NULL)) WHERE facm2.fund_allocation_id = fa.id AND fp.financial_year COLLATE utf8mb4_unicode_ci = fa.financial_year COLLATE utf8mb4_unicode_ci AND fp.duration_id COLLATE utf8mb4_unicode_ci = fa.duration_id COLLATE utf8mb4_unicode_ci AND (fp.sub_duration_id COLLATE utf8mb4_unicode_ci = fa.sub_duration_id COLLATE utf8mb4_unicode_ci OR (fp.sub_duration_id IS NULL AND fa.sub_duration_id IS NULL)))";

        $utilizationSubquery = "(SELECT COALESCE(SUM(cumd.amount_to_be_allocated), 0) 
            FROM component_utilization_mappings cum 
            INNER JOIN component_utilization_mapping_details cumd ON cum.id COLLATE utf8mb4_unicode_ci = cumd.mapping_id COLLATE utf8mb4_unicode_ci 
            INNER JOIN fund_allocation_component_mappings facm3 ON facm3.major_component_id COLLATE utf8mb4_unicode_ci = cumd.source_major_component COLLATE utf8mb4_unicode_ci AND (facm3.sub_component_id COLLATE utf8mb4_unicode_ci = cumd.source_sub_component COLLATE utf8mb4_unicode_ci OR (facm3.sub_component_id IS NULL AND cumd.source_sub_component IS NULL))
            WHERE facm3.fund_allocation_id = fa.id 
            AND cum.financial_year COLLATE utf8mb4_unicode_ci = fa.financial_year COLLATE utf8mb4_unicode_ci 
            AND cum.duration COLLATE utf8mb4_unicode_ci = fa.duration_id COLLATE utf8mb4_unicode_ci 
            AND (cum.sub_duration COLLATE utf8mb4_unicode_ci = fa.sub_duration_id COLLATE utf8mb4_unicode_ci OR (cum.sub_duration IS NULL AND fa.sub_duration_id IS NULL)))";

        $subQuery = DB::table('fund_allocations as fa')
            ->leftJoin('attribute_values as duration_av', 'fa.duration_id', '=', 'duration_av.id')
            ->leftJoin('attribute_values as sub_duration_av', 'fa.sub_duration_id', '=', 'sub_duration_av.id')
            ->leftJoin('fund_allocation_component_mappings as facm', 'fa.id', '=', 'facm.fund_allocation_id')
            ->select(
                'fa.id',
                'fa.financial_year',
                'fa.sanction_order_number',
                'fa.sanction_order_date',
                'fa.created_at',
                'duration_av.attribute_value as duration_name',
                'sub_duration_av.attribute_value as sub_duration_name',
                'total_amount_allocated as remaining_amount',
                'total_available_amount as total_available_amount',
                DB::raw('COALESCE(fa.total_amount_allocated, 0) as total_fresh_amount'),
                DB::raw('COALESCE(SUM(facm.amount), 0) as total_allocated_amount'),
               // DB::raw("((COALESCE(fa.total_amount_allocated, 0) - COALESCE(SUM(facm.amount), 0)) + $remainingAmountSubquery - $utilizationSubquery) as remaining_amount")
            )
            ->groupBy(
                'fa.id',
                'fa.financial_year',
                'fa.sanction_order_number',
                'fa.sanction_order_date',
                'fa.created_at',
                'duration_av.attribute_value',
                'sub_duration_av.attribute_value',
                'fa.total_amount_allocated',
                'fa.total_available_amount'
            );

        if (!empty($filters['financial_year'])) {
            $subQuery->where('fa.financial_year', $filters['financial_year']);
        }

        if (!empty($filters['major_component_id'])) {
            $subQuery->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('fund_allocation_component_mappings')
                    ->whereColumn('fund_allocation_id', 'fa.id')
                    ->where('major_component_id', $filters['major_component_id']);
            });
        }

        if (!empty($filters['sub_component_id'])) {
            $subQuery->whereExists(function ($q) use ($filters) {
                $q->select(DB::raw(1))
                    ->from('fund_allocation_component_mappings')
                    ->whereColumn('fund_allocation_id', 'fa.id')
                    ->where('sub_component_id', $filters['sub_component_id']);
            });
        }

        if (!empty($filters['duration_id'])) {
            $subQuery->where('fa.duration_id', $filters['duration_id']);
        }

        if (!empty($filters['sub_duration_id'])) {
            $subQuery->where('fa.sub_duration_id', $filters['sub_duration_id']);
        }

        $query = DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('financial_year', 'like', "%$search%")
                    ->orWhere('sanction_order_number', 'like', "%$search%")
                    ->orWhere('duration_name', 'like', "%$search%")
                    ->orWhere('sub_duration_name', 'like', "%$search%")
                    ->orWhere('total_amount', 'like', "%$search%");
            });
        }

        $orderColumn = $this->columns[$order] ?? 'created_at';
        $orderColumn = match($orderColumn) {
            'fa.financial_year' => 'financial_year',
            'duration_av.attribute_value' => 'duration_name',
            'sub_duration_av.attribute_value' => 'sub_duration_name',
            'fa.sanction_order_number' => 'sanction_order_number',
            'fa.sanction_order_date' => 'sanction_order_date',
            'fa.total_amount_allocated' => 'total_fresh_amount',
            'fa.total_available_amount' => 'total_available_amount',
            
            'fa.created_at' => 'created_at',
            default => $orderColumn
        };

        $query->orderBy($orderColumn, $dir);

        if ($page) {
            return $this->getDataTableResult(
                FundAllocationResource::collection($query->paginate($limit))
            );
        }

        return FundAllocationResource::collection($query->get());
    }
}
