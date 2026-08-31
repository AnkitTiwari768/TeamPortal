<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListFundDistributionTransactionsAction
{
    use DataTable;

    protected array $columns = [
        1 => 'fdis.source_type',
        2 => 'fdis.sanction_order_date',
        3 => 'd1.attribute_value',
        4 => 'fdis.distribution_amount',
        5 => 'fdis.tds_percentage',
        6 => 'fdis.net_payable_amount'
    ];

    public function execute(string $fy, string $majorId, ?string $subId): array
    {
        [$limit, $order, $dir, $search, $page, $filters, $start] = $this->getDataTableParams(null, $this->columns);

        $query = DB::table('fund_distributions as fdis')
            ->leftJoin('attribute_values as d1', DB::raw('CONVERT(d1.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fdis.duration_id')
            ->leftJoin('attribute_values as d2', DB::raw('CONVERT(d2.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fdis.sub_duration_id')
            ->select(
                'fdis.*',
                'd1.attribute_value as duration_name',
                'd2.attribute_value as sub_duration_name'
            )
            ->whereNull('fdis.deleted_at')
            ->where('fdis.financial_year', $fy)
            ->where('fdis.major_component_id', $majorId);

        if ($subId && $subId !== 'null' && $subId !== 'NULL') {
            $query->where('fdis.sub_component_id', $subId);
        } else {
            $query->whereNull('fdis.sub_component_id');
        }

        // Base Count
        $recordsTotal = $query->count();

        // Standard Filters
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
             try {
                 $fromRaw = str_replace('/', '-', $filters['from_date']);
                 $toRaw   = str_replace('/', '-', $filters['to_date']);
                 
                 $from = \Carbon\Carbon::createFromFormat('d-m-Y', $fromRaw)->startOfDay();
                 $to   = \Carbon\Carbon::createFromFormat('d-m-Y', $toRaw)->endOfDay();
                 $query->whereBetween('fdis.sanction_order_date', [$from, $to]);
             } catch (\Exception $e) {}
        }

        if (!empty($filters['source_type'])) {
            $query->where('fdis.source_type', $filters['source_type']);
        }

        // Global Text Search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fdis.source_type', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.sanction_order_number', 'LIKE', "%{$search}%")
                  ->orWhere('d1.attribute_value', 'LIKE', "%{$search}%")
                  ->orWhere('d2.attribute_value', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.remarks', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.distribution_amount', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.tds_percentage', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.net_payable_amount', 'LIKE', "%{$search}%")
                  ->orWhere(DB::raw("DATE_FORMAT(fdis.sanction_order_date, '%d-%m-%Y')"), 'LIKE', "%{$search}%")
                  ->orWhere(DB::raw("DATE_FORMAT(fdis.sanction_order_date, '%d/%m/%Y')"), 'LIKE', "%{$search}%");
            });
        }

        // Filtered Count
        $recordsFiltered = $query->count();

        // Sorting
        $direction = $dir ?: 'desc';
        $orderCol  = (!empty($order) && $order != 'id') ? $order : 'fdis.created_at';
        $query->orderBy($orderCol, $direction);

        // Fetch paginated results
        $results = $query->offset((int)$start)
                         ->limit($limit > 0 ? $limit : 10)
                         ->get();

        return [
            'draw'            => intval(request('draw', 1)),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $results
        ];
    }
}
