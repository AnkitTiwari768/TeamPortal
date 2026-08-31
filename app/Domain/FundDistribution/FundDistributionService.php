<?php

declare(strict_types=1);

namespace App\Domain\FundDistribution;

use Illuminate\Support\Facades\DB;
use App\Domain\FundDistribution\FundDistribution;
use App\Web\Allocation\PoolService;
use App\Domain\FundAllocation\FundAllocationService;

class FundDistributionService
{
    public function __construct(private ?FundAllocationService $fundAllocationService = null)
    {
        $this->fundAllocationService ??= new FundAllocationService();
    }

    /**
     * When no pool exists at the exact selected (duration/sub-duration) level, check
     * whether a broader, chronologically-enclosing period (e.g. the Half-Year a Month or
     * Quarter falls within) already has a Fund Allocation for this component, and if so
     * return a warning payload with that period's available balance instead of a plain
     * "no allocation" dead end. Delegates the actual period-comparison to
     * FundAllocationService::findAncestorPeriodWithAllocation() so both modules agree on
     * the same financial-year timeline instead of maintaining two copies of it.
     */
    public function findParentPeriodAllocationWarning(
        string $financialYear,
        string $durationId,
        ?string $subDurationId,
        string $majorComponentId,
        ?string $subComponentId
    ): array {
        if (empty($subDurationId)) {
            return [];
        }

        $ancestor = $this->fundAllocationService->findAncestorPeriodWithAllocation(
            $financialYear,
            $durationId,
            $subDurationId,
            $majorComponentId,
            $subComponentId
        );

        if (!$ancestor) {
            return [];
        }

        $poolService = app(PoolService::class);
        $ancestorKey = $poolService->resolvePoolKey([
            'financial_year' => $financialYear,
            'duration_id' => $ancestor['duration_id'],
            'sub_duration_id' => $ancestor['sub_duration_id'],
            'major_component_id' => $majorComponentId,
            'sub_component_id' => $subComponentId,
        ]);
        $ancestorPool = $poolService->findPool($ancestorKey);
        $availableBalance = $ancestorPool ? (float) $ancestorPool->remaining_balance : 0.0;

        return [
            'parent_period_warning' => true,
            'message' => "Allocation already exists for the {$ancestor['sub_duration_name']}. Available balance: ₹" . number_format($availableBalance, 2) . '.',
            'available_balance' => $availableBalance,
            'parent_period_name' => $ancestor['sub_duration_name'],
        ];
    }

    /**
     * Sum of distributions already made under any FINER period chronologically contained
     * within the given (duration/sub-duration) -- e.g. selecting Half-Yearly/First-Half
     * should surface what Monthly/July (April-September) already distributed, since that
     * money is not visible in the Half-Yearly pool's own total_distributed_amount under
     * Strict pooling (each period tracks its own pool). Uses the same fiscal-month-range
     * comparison as findAncestorPeriodWithAllocation() so both directions of the
     * relationship agree. Returns 0 for Monthly (no finer period exists beneath it) or
     * when the selected period can't be resolved to a range (e.g. Yearly).
     */
    public function getChildPeriodsDistributedAmount(
        string $financialYear,
        string $durationId,
        ?string $subDurationId,
        string $majorComponentId,
        ?string $subComponentId
    ): float {
        if (empty($subDurationId)) {
            return 0.0;
        }

        $selectedRange = $this->fundAllocationService->getFiscalMonthRange($durationId, $subDurationId);
        if (!$selectedRange) {
            return 0.0;
        }
        [$selectedStart, $selectedEnd] = $selectedRange;

        $rows = DB::table('fund_distributions')
            ->whereNull('deleted_at')
            ->where('financial_year', $financialYear)
            ->where('major_component_id', $majorComponentId)
            ->when(
                !empty($subComponentId),
                fn ($q) => $q->where('sub_component_id', $subComponentId),
                fn ($q) => $q->whereNull('sub_component_id')
            )
            ->select('duration_id', 'sub_duration_id', 'distribution_amount')
            ->get();

        $total = 0.0;
        foreach ($rows as $row) {
            if ($row->duration_id === $durationId && $row->sub_duration_id === $subDurationId) {
                continue;
            }

            $range = $this->fundAllocationService->getFiscalMonthRange($row->duration_id, $row->sub_duration_id);
            if (!$range) {
                continue;
            }
            [$start, $end] = $range;

            if ($start >= $selectedStart && $end <= $selectedEnd) {
                $total += (float) $row->distribution_amount;
            }
        }

        return $total;
    }

    /**
     * Get details for editing a fund distribution record.
     */
    public function getFundDistributionEditDetails(string $id): array
    {
        $fundDistribution = FundDistribution::findOrFail($id);

        // Pre-resolve existing cascading lists
        $subComponents = $this->getAttributeValues('major-components', $fundDistribution->major_component_id);
        $subDurations = [];
        if ($fundDistribution->duration_id) {
            $subDurations = $this->getAttributeValues('duration', $fundDistribution->duration_id);
        }

        return [
            'fundDistribution' => $fundDistribution->toArray(),
            'subComponents' => $subComponents,
            'subDurations' => $subDurations,
            'documentUrl' => $this->getDocumentUrl($fundDistribution->upload_document),
            'id' => $id,
        ];
    }

    /**
     * Get details for viewing an individual transaction.
     */
    public function getFundDistributionViewDetails(string $id): array
    {
        $fundDistribution = FundDistribution::findOrFail($id);

        $attributeIds = collect([
            $fundDistribution->duration_id,
            $fundDistribution->sub_duration_id,
            $fundDistribution->major_component_id,
            $fundDistribution->sub_component_id,
        ])->filter()->unique();

        $attributeValues = DB::table('attribute_values')
            ->whereIn('id', $attributeIds)
            ->pluck('attribute_value', 'id');

        // Use the fund_pool_id FK recorded at the time of this transaction rather than
        // re-deriving the pool key from financial_year/duration/component -- resolving a
        // fresh key can disagree with the pool the transaction was actually debited
        // against (e.g. if the pooling mode config changes after older transactions were
        // recorded), while fund_pool_id always points at the exact right pool.
        $pool = $fundDistribution->fund_pool_id
            ? DB::table('fund_pools')->where('id', $fundDistribution->fund_pool_id)->first()
            : null;

        return [
            'row' => array_merge(
                $fundDistribution->toArray(),
                [
                    'major_component_name' => $attributeValues[$fundDistribution->major_component_id] ?? 'N/A',
                    'sub_component_name' => $attributeValues[$fundDistribution->sub_component_id] ?? 'N/A',
                    'duration_name' => $attributeValues[$fundDistribution->duration_id] ?? 'Consolidated',
                    'sub_duration_name' => $attributeValues[$fundDistribution->sub_duration_id] ?? 'N/A',
                    'sanction_order_date_formatted' => $fundDistribution->sanction_order_date ? $fundDistribution->sanction_order_date->format('d-m-Y') : null,
                ]
            ),
            'documentUrl' => $this->getDocumentUrl($fundDistribution->upload_document),
            'breakdown' => $pool,
        ];
    }

    /**
     * Detail Drilldown Data Generator.
     */
    public function getDrillDownGroupDetails(string $fy, string $majorId, ?string $subId): array
    {
        $hasSubId = $subId && $subId !== 'null' && $subId !== 'NULL';

        // 1. Fetch Aggregate Totals (Cards)
        $totalsQuery = DB::table('fund_pools')
            ->where('financial_year', $fy)
            ->where('major_component_id', $majorId);

        if ($hasSubId) {
            $totalsQuery->where('sub_component_id', $subId);
        } else {
            $totalsQuery->whereNull('sub_component_id');
        }

        $totals = $totalsQuery->select(
            DB::raw('COALESCE(SUM(total_allocated_amount), 0) as total_allocated'),
            DB::raw('COALESCE(SUM(total_distributed_amount), 0) as total_distributed'),
            DB::raw('COALESCE(SUM(remaining_balance), 0) as remaining')
        )->first();

        // fund_pools.total_allocated_amount is NET of any Component Utilization Mapping
        // transfers, same as in ListFundDistributionAction -- add back whatever this
        // component has transferred away so this card matches the List's "Allocation"
        // figure for the exact same fy/major/sub the user clicked through from.
        $transferredAway = (float) DB::selectOne(
            "SELECT COALESCE(SUM(cumd.amount_to_be_allocated), 0) as amt
             FROM component_utilization_mapping_details cumd
             INNER JOIN component_utilization_mappings cum ON cum.id COLLATE utf8mb4_unicode_ci = cumd.mapping_id COLLATE utf8mb4_unicode_ci
             WHERE cum.financial_year COLLATE utf8mb4_unicode_ci = ?
             AND cumd.source_major_component = ?
             AND (cumd.source_sub_component = ? OR (cumd.source_sub_component IS NULL AND ? IS NULL))",
            [$fy, $majorId, $hasSubId ? $subId : null, $hasSubId ? $subId : null]
        )->amt;

        $totals->total_allocated = (float) $totals->total_allocated + $transferredAway;
        $totals->total_transferred_out = $transferredAway;

        // 2. Build Component Names Reference
        $majorName = DB::table('attribute_values')->where('id', $majorId)->value('attribute_value');
        $subName = $hasSubId ? DB::table('attribute_values')->where('id', $subId)->value('attribute_value') : null;

        // 3. Fetch Durational Breakdowns
        $breakdownQuery = DB::table('fund_pools as fp')
            ->leftJoin('attribute_values as dur', DB::raw('CONVERT(dur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.duration_id')
            ->leftJoin('attribute_values as sdur', DB::raw('CONVERT(sdur.id USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'fp.sub_duration_id')
            ->where('fp.financial_year', $fy)
            ->where('fp.major_component_id', $majorId);

        if ($hasSubId) {
            $breakdownQuery->where('fp.sub_component_id', $subId);
        } else {
            $breakdownQuery->whereNull('fp.sub_component_id');
        }

        // Same add-back as above, but correlated to each row's own duration/sub-duration
        // so a component with transfers spread across multiple periods doesn't have the
        // full transferred-away amount double-counted onto every breakdown row.
        $transferredAwayPerRow = "(SELECT COALESCE(SUM(cumd.amount_to_be_allocated), 0)
            FROM component_utilization_mapping_details cumd
            INNER JOIN component_utilization_mappings cum ON cum.id COLLATE utf8mb4_unicode_ci = cumd.mapping_id COLLATE utf8mb4_unicode_ci
            WHERE cum.financial_year COLLATE utf8mb4_unicode_ci = fp.financial_year COLLATE utf8mb4_unicode_ci
            AND cum.duration COLLATE utf8mb4_unicode_ci = fp.duration_id COLLATE utf8mb4_unicode_ci
            AND (cum.sub_duration COLLATE utf8mb4_unicode_ci = fp.sub_duration_id COLLATE utf8mb4_unicode_ci OR (cum.sub_duration IS NULL AND fp.sub_duration_id IS NULL))
            AND cumd.source_major_component = fp.major_component_id
            AND (cumd.source_sub_component = fp.sub_component_id OR (cumd.source_sub_component IS NULL AND fp.sub_component_id IS NULL)))";

        $breakdowns = $breakdownQuery->select(
            'fp.duration_id',
            'fp.sub_duration_id',
            DB::raw('COALESCE(dur.attribute_value, "Consolidated Period") as duration_name'),
            'sdur.attribute_value as sub_duration_name',
            DB::raw("(fp.total_allocated_amount + $transferredAwayPerRow) as total_allocated_amount"),
            'fp.total_distributed_amount',
            'fp.remaining_balance'
        )->orderBy('duration_name')->get();

        // Label each Quarterly/Monthly row with the fiscal half-year it falls within
        // (First Half = Q1+Q2 = April-September, Second Half = Q3+Q4 = October-March),
        // so the breakdown table visibly reflects the fiscal quarter/half-year mapping.
        $breakdowns->each(function ($breakdown) {
            $breakdown->half_year_label = ($breakdown->duration_id && $breakdown->sub_duration_id)
                ? $this->fundAllocationService->resolveHalfYearLabel($breakdown->duration_id, $breakdown->sub_duration_id)
                : null;
        });

        return compact('totals', 'majorName', 'subName', 'breakdowns');
    }

    /**
     * Helper to load attribute values by attribute code and parent id.
     */
    public function getAttributeValues(string $codeOrId, ?string $parentId = null)
    {
        $byCode = DB::table('attributes')->where('code', $codeOrId)->first();

        $query = DB::table('attribute_values as av')
            ->where('av.status', 1)
            ->select(
                'av.id',
                'av.attribute_value as name',
                'av.code',
                'av.parent_id'
            );

        if ($byCode) {
            $query->where('av.attribute_id', $byCode->id);
        } else {
            $query->where('av.attribute_id', $codeOrId);
        }

        if ($parentId !== null) {
            $query->where('av.parent_id', $parentId);
        } else {
            $query->whereNull('av.parent_id');
        }

        return $query->orderBy('av.sort_order')->get();
    }

    /**
     * Get document details for viewing or downloading.
     */
    public function getDocumentDetails(string $id): ?array
    {
        try {
            $fundDistribution = FundDistribution::findOrFail($id);
            if (!$fundDistribution || empty($fundDistribution->upload_document)) {
                return null;
            }

            $file = DB::table('file_uploads')
                ->where('id', $fundDistribution->upload_document)
                ->orWhere('file_system_name', $fundDistribution->upload_document)
                ->first();

            if (!$file) {
                return null;
            }

            return [
                'file_path' => $file->file_path,
                'file_name' => $file->file_name,
                'file_type' => $file->file_type,
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Resolve uploaded document URL.
     */
    private function getDocumentUrl(?string $documentPath): ?string
    {
        if (empty($documentPath)) {
            return null;
        }

        $file = DB::table('file_uploads')
            ->where('id', $documentPath)
            ->orWhere('file_system_name', $documentPath)
            ->first();

        return $file
            ? asset($file->file_path)
            : asset('storage/' . $documentPath);
    }
}
