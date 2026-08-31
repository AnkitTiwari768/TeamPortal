<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use App\Http\Services\ApiService;
use App\Traits\HasFileUpload;
use App\Traits\DataTable;
use App\Web\Allocation\PoolService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DistributionService extends ApiService
{
    use DataTable, HasFileUpload;

    protected array $columns = [
        1 => 'financial_year',
        2 => 'major_component_name',
        3 => 'sub_component_name',
        4 => 'total_allocated',
        5 => 'total_distributed',
        6 => 'remaining'
    ];

    protected array $detailColumns = [
        1 => 'fdis.source_type',
        2 => 'fdis.sanction_order_date',
        3 => 'd1.attribute_value',
        4 => 'fdis.distribution_amount',
        5 => 'fdis.tds_percentage',
        6 => 'fdis.net_payable_amount'
    ];

    /**
     * Aggregated Summary List Builder
     * Pulls directly from consolidated pools for blazing-fast enterprise reporting.
     */
    public function getAggregatedSummaryList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

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
            ->havingRaw('SUM(fp.total_distributed_amount) > 0');
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
            $query->having('financial_year', 'LIKE', "%{$s}%")
                  ->orHaving('major_component_name', 'LIKE', "%{$s}%")
                  ->orHaving('sub_component_name', 'LIKE', "%{$s}%");
        }

        // Dynamic Sorting Handler
        $orderCol = $this->columns[$order] ?? 'total_allocated';
        $direction = $dir ?: 'desc';

        // Sorting requires manual implementation for raw combined select strings
        if (in_array($orderCol, ['total_allocated', 'total_distributed', 'remaining'])) {
             $query->orderByRaw("{$orderCol} {$direction}");
        } else {
             $query->orderBy($orderCol, $direction);
        }

        // Enforce robust server-side pagination delivery
        $totalRows = DB::table(DB::raw("({$query->toSql()}) as sub"))
                       ->setBindings($query->getBindings())
                       ->count();

        $results = $query->offset((int)request('start', 0))
                         ->limit($limit > 0 ? $limit : 10)
                         ->get();

        return [
            'draw'            => intval(request('draw', 1)),
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => $totalRows,
            'data'            => $results
        ];
    }

    /**
     * Detail Drilldown Data Generator
     */
    public function getDrillDownGroupDetails(string $fy, string $majorId, ?string $subId)
    {
        // 1. Fetch Aggregate Totals (Cards)
        $totals = DB::table('fund_pools')
            ->where('financial_year', $fy)
            ->where('major_component_id', $majorId);
        
        if ($subId && $subId !== 'null' && $subId !== 'NULL') {
            $totals->where('sub_component_id', $subId);
        } else {
            $totals->whereNull('sub_component_id');
        }

        $totals = $totals->select(
            DB::raw('SUM(total_allocated_amount) as total_allocated'),
            DB::raw('SUM(total_distributed_amount) as total_distributed'),
            DB::raw('SUM(remaining_balance) as remaining')
        )->first();

        // 2. Build Component Names Reference
        $majorName = DB::table('attribute_values')->where('id', $majorId)->value('attribute_value');
        $subName = $subId ? DB::table('attribute_values')->where('id', $subId)->value('attribute_value') : null;

        // 3. Fetch Durational Breakdowns
        $breakdownQuery = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as dur', DB::raw('CONVERT(dur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.duration_id')
            ->leftJoin('attribute_values as sdur', DB::raw('CONVERT(sdur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.sub_duration_id')
            ->where('fp.financial_year', $fy)
            ->where('fp.major_component_id', $majorId);

        if ($subId && $subId !== 'null' && $subId !== 'NULL') {
            $breakdownQuery->where('fp.sub_component_id', $subId);
        } else {
            $breakdownQuery->whereNull('fp.sub_component_id');
        }

        $breakdowns = $breakdownQuery->select(
            DB::raw('COALESCE(dur.attribute_value, "Consolidated Period") as duration_name'),
            'sdur.attribute_value as sub_duration_name',
            'fp.total_allocated_amount',
            'fp.total_distributed_amount',
            'fp.remaining_balance'
        )->orderBy('duration_name')->get();

        return compact('totals', 'majorName', 'subName', 'breakdowns');
    }

    /**
     * Explicit Transactional Table for Detail Page
     */
    public function getRawTransactionsList(string $fy, string $majorId, ?string $subId)
    {
        // Temporarily switch schema property context to resolve trait retrieval correctly
        $this->columns = $this->detailColumns;
        
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

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

        // B: Capture Base Count for Unfiltered Dataset
        $recordsTotal = $query->count();

        // C: Apply Standardized Filters from Interface
        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
             try {
                 // Normalize input to guarantee consistent hyphenated parsing regardless of delimiter typed
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

        // D: Execute Dynamic Global Text Search Engine
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fdis.source_type', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.sanction_order_number', 'LIKE', "%{$search}%")
                  ->orWhere('d1.attribute_value', 'LIKE', "%{$search}%")
                  ->orWhere('d2.attribute_value', 'LIKE', "%{$search}%")
                  ->orWhere('fdis.remarks', 'LIKE', "%{$search}%")
                  // Enable date matching via explicit format projection
                  ->orWhere(DB::raw("DATE_FORMAT(fdis.sanction_order_date, '%d-%m-%Y')"), 'LIKE', "%{$search}%")
                  ->orWhere(DB::raw("DATE_FORMAT(fdis.sanction_order_date, '%d/%m/%Y')"), 'LIKE', "%{$search}%");
            });
        }

        // E: Capture Filtered Count
        $recordsFiltered = $query->count();

        // F: Deploy Responsive Native Sorting
        $direction = $dir ?: 'desc';
        $orderCol  = (!empty($order) && $order != 'id') ? $order : 'fdis.created_at';
        $query->orderBy($orderCol, $direction);

        // G: Deliver Fully Hydrated Results Stream
        $results = $query->offset((int)request('start', 0))
                         ->limit($limit > 0 ? $limit : 10)
                         ->get();

        return [
            'draw'            => intval(request('draw', 1)),
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $results
        ];
    }

    /**
     * Transaction-Locked Storage Mechanism
     */
    public function processSafeStore(array $data, ?string $editId = null)
    {
        return DB::transaction(function () use ($data, $editId) {
            
            $poolService = app(PoolService::class);

            // A: Standard Pool Resolution Key Builder
            $keyData = [
                'financial_year'    => $data['financial_year'],
                'duration_id'       => $data['duration_id'],
                'sub_duration_id'   => (empty($data['sub_duration_id']) || strtolower((string)$data['sub_duration_id']) == 'null') ? null : $data['sub_duration_id'],
                'major_component_id'=> $data['major_component_id'],
                'sub_component_id'  => (empty($data['sub_component_id']) || strtolower((string)$data['sub_component_id']) == 'null') ? null : $data['sub_component_id'],
            ];

            $key = $poolService->resolvePoolKey($keyData);
            
            // **FORCE EXPLICIT VALIDATION**: Pool existence mandatory!
            $pool = $poolService->findPool($key, true); // Acquire Row Lock immediately
            if (!$pool) {
                 throw new \Exception("Operational Error: No allocation pool discovered for these dimensions. Please create an allocation first.");
            }

            // Perform Numeric Normalization
            $distAmount = (float) $data['distribution_amount'];
            $tdsPercent = (float) $data['tds_percentage'];
            $tdsAmount = ($distAmount * $tdsPercent) / 100;
            $netPayable = $distAmount - $tdsAmount;

            $recordData = [
                'financial_year'      => $data['financial_year'],
                'duration_id'         => $data['duration_id'],
                'sub_duration_id'     => $data['sub_duration_id'] ?? null,
                'major_component_id'  => $data['major_component_id'],
                'sub_component_id'    => $data['sub_component_id'] ?? null,
                'fund_pool_id'        => $pool->id,
                
                'distribution_amount' => $distAmount,
                'tds_percentage'      => $tdsPercent,
                'tds_amount'          => $tdsAmount,
                'net_payable_amount'  => $netPayable,
                
                'sanction_order_number' => $data['sanction_order_number'] ?? null,
                'sanction_order_date'   => !empty($data['sanction_order_date']) ? date('Y-m-d', strtotime($data['sanction_order_date'])) : null,
                'remarks'             => $data['remarks'] ?? null,
                
                'upload_document'     => $data['upload_document'] ?? null,
                'upload_document_original_name' => isset($data['upload_document']) ? self::originalName($data['upload_document']) : null,
                'updated_at'          => now(),
                'updated_by'          => AuthId()
            ];

            if ($editId) {
                // --- REVERSE EXISTING IMPACT ---
                $old = DB::table('fund_distributions')->where('id', $editId)->first();
                if ($old) {
                    // Reversing the impact before re-applying
                    $poolService->updatePoolDistribution($key, -((float)$old->distribution_amount));
                }
                
                DB::table('fund_distributions')->where('id', $editId)->update($recordData);
                
                // --- APPLY NEW IMPACT ---
                $poolService->updatePoolDistribution($key, $distAmount);

            } else {
                // --- CREATE BRANCH ---
                $recordData['id'] = (string) Str::uuid();
                $recordData['source_type'] = 'MANUAL';
                $recordData['created_at'] = now();
                $recordData['created_by'] = AuthId();

                DB::table('fund_distributions')->insert($recordData);
                
                // --- APPLY POSITIVE DEDUCTION IMPACT ---
                $poolService->updatePoolDistribution($key, $distAmount);
            }

            return true;
        });
    }

    public function getRecord(string $id)
    {
         $row = DB::table('fund_distributions as fd')
            ->leftJoin('file_uploads as f1', DB::raw('CONVERT(f1.file_system_name USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fd.upload_document')
             ->select('fd.*', 'f1.file_path as doc_link', 'f1.id as file_upload_uuid')
             ->where('fd.id', $id)
             ->first();

         if($row && $row->sanction_order_date) {
              $row->sanction_order_date_formatted = date('d-m-Y', strtotime($row->sanction_order_date));
         }
         return $row;
    }

    /**
     * Safe Deletion handler with automatic reverse deductions
     */
    public function performSafeDelete(string $id)
    {
        return DB::transaction(function() use ($id) {
             $row = DB::table('fund_distributions')->where('id', $id)->first();
             if (!$row) return false;

             // 1. Build key for explicit Pool Lookups
             $keyData = [
                'financial_year'    => $row->financial_year,
                'duration_id'       => $row->duration_id,
                'sub_duration_id'   => $row->sub_duration_id,
                'major_component_id'=> $row->major_component_id,
                'sub_component_id'  => $row->sub_component_id,
             ];

             $poolService = app(PoolService::class);
             $key = $poolService->resolvePoolKey($keyData);

             // 2. Back-out calculation impact from relevant Pool
             $poolService->updatePoolDistribution($key, -((float)$row->distribution_amount));

             // 3. Perform Soft-Delete safety lock
             return DB::table('fund_distributions')
                      ->where('id', $id)
                      ->update([
                          'deleted_at' => now(),
                          'updated_at' => now(),
                          'updated_by' => AuthId()
                      ]);
        });
    }
}
