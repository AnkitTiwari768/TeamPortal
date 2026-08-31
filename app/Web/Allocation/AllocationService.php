<?php

declare(strict_types=1);

namespace App\Web\Allocation;

use Illuminate\Support\Facades\DB;
use App\Http\Services\ApiService;
use App\Traits\HasFileUpload;
use App\Traits\DataTable;
use Carbon\Carbon;

/**
 * AllocationService
 *
 * Business logic for the Allocation Module.
 * Handles CRUD for allocation_headers, allocation_lines, and fund_pools.
 */
class AllocationService extends ApiService
{
    use DataTable, HasFileUpload;

    protected array $columns = [
        1 => 'financial_year',
        2 => 'duration_id',
        3 => 'sanction_order_number',
        4 => 'sanction_order_date',
        5 => 'created_at',
    ];

    /* ─────────────────────────────────────────────────────────────
     | LISTING
     ───────────────────────────────────────────────────────────── */
    public function getAllocationList(): array
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('allocation_headers as ah')
            ->leftJoin('allocation_lines as al', 'al.allocation_header_id', '=', 'ah.id')
            ->leftJoin('attribute_values as dur', 'dur.id', '=', 'ah.duration_id')
            ->leftJoin('attribute_values as sdur', 'sdur.id', '=', 'ah.sub_duration_id')
            ->select(
                'ah.id',
                'ah.financial_year',
                'ah.duration_id',
                'ah.sub_duration_id',
                'ah.sanction_order_number',
                'ah.sanction_order_date',
                'ah.remarks',
                'ah.document_path',
                'ah.created_at',
                'ah.updated_at',
                'dur.attribute_value as duration_name',
                'sdur.attribute_value as sub_duration_name',
                DB::raw('COALESCE(SUM(al.amount), 0) as total_amount'),
                DB::raw("DATE_FORMAT(ah.sanction_order_date, '%d-%m-%Y') as sanction_order_date_fmt"),
                DB::raw("DATE_FORMAT(ah.created_at, '%d-%m-%Y') as created_at_fmt")
            )
            ->groupBy(
                'ah.id',
                'ah.financial_year',
                'ah.duration_id',
                'ah.sub_duration_id',
                'ah.sanction_order_number',
                'ah.sanction_order_date',
                'ah.remarks',
                'ah.document_path',
                'ah.created_at',
                'ah.updated_at',
                'dur.attribute_value',
                'sdur.attribute_value'
            );

        /* ───────────────────────── SEARCH ───────────────────────── */
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('ah.financial_year', 'like', "%{$search}%")
                    ->orWhere('ah.sanction_order_number', 'like', "%{$search}%")
                    ->orWhere('ah.remarks', 'like', "%{$search}%")
                    ->orWhere('dur.attribute_value', 'like', "%{$search}%")
                    ->orWhere('sdur.attribute_value', 'like', "%{$search}%")
                    // Multi-Axis Formatted Dates
                    ->orWhere(DB::raw("DATE_FORMAT(ah.sanction_order_date, '%d-%m-%Y')"), 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("DATE_FORMAT(ah.sanction_order_date, '%d/%m/%Y')"), 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("DATE_FORMAT(ah.created_at, '%d-%m-%Y')"), 'LIKE', "%{$search}%")
                    ->orWhere(DB::raw("DATE_FORMAT(ah.created_at, '%d/%m/%Y')"), 'LIKE', "%{$search}%")
                    // High-Performance Inline Aggregation Targeting for Global Amounts
                    ->orWhereRaw("(SELECT COALESCE(SUM(amount), 0) FROM allocation_lines WHERE allocation_header_id = ah.id) LIKE ?", ["%{$search}%"]);
            });
        }

        /* ───────────────────────── FILTERS ───────────────────────── */
        // DataTable sends these as top-level GET params (not nested under filters[])
        $fyFilter = request('financial_year');
        $majorFilter = request('major_component_id');
        $subCompFilter = request('sub_component_id');

        if (!empty($fyFilter)) {
            $query->where('ah.financial_year', $fyFilter);
        }

        if (!empty($majorFilter)) {
            $query->where('al.major_component_id', $majorFilter);
        }

        if (!empty($subCompFilter)) {
            $query->where('al.sub_component_id', $subCompFilter);
        }

        /* ───────────────────────── ORDER ───────────────────────── */
        $orderColumn = $this->columns[$order] ?? 'created_at';
        $query->orderBy("ah.{$orderColumn}", $dir ?: 'desc');

        /* ───────────────────────── PAGINATION ───────────────────────── */
        if ($page) {
            $paginator = $query->paginate($limit);

            // 🔥 IMPORTANT: convert stdClass → array properly
            $data = collect($paginator->items())->map(function ($item) {
                return (array) $item;
            })->values()->toArray();

            return [
                'draw' => (int) request('draw', 0),
                'recordsTotal' => $paginator->total(),
                'recordsFiltered' => $paginator->total(),
                'data' => $data,
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
            ];
        }

        /* ───────────────────────── NON-PAGINATED ───────────────────────── */
        $data = $query->get()->map(function ($item) {
            return (array) $item;
        })->values()->toArray();

        return [
            'draw' => (int) request('draw', 0),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ];
    }

    /* ─────────────────────────────────────────────────────────────
     | SINGLE HEADER DETAIL
     ───────────────────────────────────────────────────────────── */
    public function getHeaderDetail(string $id): array // Changed from object to array
    {
        $detail = DB::table('allocation_headers as ah')
            ->leftJoin('file_uploads as fu', DB::raw('CONVERT(fu.file_system_name USING utf8mb4) COLLATE utf8mb4_unicode_ci'), '=', 'ah.document_path')
            ->leftJoin('attribute_values as dur', 'dur.id', '=', 'ah.duration_id')
            ->leftJoin('attribute_values as sdur', 'sdur.id', '=', 'ah.sub_duration_id')
            ->select(
                'ah.*',
                'dur.attribute_value as duration_label',
                'sdur.attribute_value as sub_duration_label',
                'fu.file_path as document_url',
                DB::raw("DATE_FORMAT(ah.sanction_order_date, '%d-%m-%Y') as sanction_order_date_fmt"),
                DB::raw("DATE_FORMAT(ah.created_at, '%d-%m-%Y %H:%i') as created_at_fmt")
            )
            ->where('ah.id', $id)
            ->first();

        if (!$detail) {
            abort(404, 'Allocation record not found');
        }

        return (array) $detail; // Convert to array here instead of controller
    }

    /* ─────────────────────────────────────────────────────────────
     | ALLOCATION LINES
     ───────────────────────────────────────────────────────────── */
    public function getLines(string $headerId)
    {
        return DB::table('allocation_lines as al')
            ->leftJoin('attribute_values as mc', 'mc.id', '=', 'al.major_component_id')
            ->leftJoin('attribute_values as sc', 'sc.id', '=', 'al.sub_component_id')
            ->select(
                'al.*',
                'mc.attribute_value as major_component_name',
                'sc.attribute_value as sub_component_name'
            )
            ->where('al.allocation_header_id', $headerId)
            ->get();
    }

    /* ─────────────────────────────────────────────────────────────
     | STORE (Create + Update)
     ───────────────────────────────────────────────────────────── */
    public function storeAllocation(array $payload, ?string $id = null): bool
    {
        return DB::transaction(function () use ($payload, $id) {

            $headerData = [
                'financial_year' => $payload['financial_year'],
                'duration_id' => $payload['duration_id'],
                'sub_duration_id' => $payload['sub_duration_id'] ?? null,
                'opening_balance' => isset($payload['opening_balance']) ? (float) $payload['opening_balance'] : 0.00,
                'sanction_order_number' => $payload['sanction_order_number'],
                'sanction_order_date' => $this->parseDate($payload['sanction_order_date']),
                'document_path' => $payload['document_path'] ?? null,
                'remarks' => $payload['remarks'] ?? null,
            ];

            if ($id) {
                // ── EDIT MODE ──────────────────────────────────────────
                // STEP 1: Fetch OLD allocation data
                $oldHeader = DB::table('allocation_headers')->where('id', $id)->first();
                $oldLines = DB::table('allocation_lines')->where('allocation_header_id', $id)->get();

                if ($oldHeader) {
                    // STEP 2: REVERSE OLD IMPACT
                    $this->reverseOldAllocations($oldHeader, $oldLines);
                }

                // STEP 3: Update Allocation header
                $headerId = $id;
                $headerData['updated_by'] = AuthId();
                $headerData['updated_at'] = now();
                DB::table('allocation_headers')->where('id', $id)->update($headerData);

                // Build new lines array
                $lines = [];

                foreach ($payload['allocation_lines'] ?? [] as $line) {
                    $lineId = $line['id'] ?? null;
                    $amount = (float) ($line['amount'] ?? 0);

                    if ($amount <= 0) {
                        continue;
                    }

                    $lines[] = [
                        'id' => $lineId ?? (string) \Illuminate\Support\Str::uuid(),
                        'allocation_header_id' => $headerId,
                        'major_component_id' => $line['major_component_id'],
                        'sub_component_id' => $line['sub_component_id'] ?? null,
                        'amount' => $amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // Delete old lines and re-insert new ones
                DB::table('allocation_lines')->where('allocation_header_id', $id)->delete();
                if (!empty($lines)) {
                    DB::table('allocation_lines')->insert($lines);
                }

                // STEP 4: APPLY NEW IMPACT
                $this->applyNewAllocations($headerData, $lines);

            } else {
                // ── CREATE MODE ─────────────────────────────────────────
                $headerId = (string) \Illuminate\Support\Str::uuid();
                $headerData['id'] = $headerId;
                $headerData['created_by'] = AuthId();
                $headerData['created_at'] = now();
                $headerData['updated_at'] = now();

                DB::table('allocation_headers')->insert($headerData);

                // --- AUTOMATIC CARRY FORWARD LOGIC ---
                $applyCarryForward = isset($payload['apply_carry_forward']) ? (bool) $payload['apply_carry_forward'] : false;
                
                if ($applyCarryForward && !empty($payload['sub_duration_id'])) {
                    $currentSubDuration = DB::table('attribute_values')->where('id', $payload['sub_duration_id'])->first();
                    if ($currentSubDuration) {
                        $previousSubDurationIds = DB::table('attribute_values')
                            ->where('parent_id', $payload['duration_id'])
                            ->where('sort_order', '<', $currentSubDuration->sort_order)
                            ->pluck('id');

                        $poolsToCarryForward = [];

                        if ($previousSubDurationIds->isNotEmpty()) {
                            $previousPools = DB::table('fund_pools')
                                ->where('financial_year', $payload['financial_year'])
                                ->where('duration_id', $payload['duration_id'])
                                ->whereIn('sub_duration_id', $previousSubDurationIds)
                                ->where('remaining_balance', '>', 0)
                                ->get();
                            
                            foreach ($previousPools as $pool) {
                                $poolsToCarryForward[] = $pool;
                            }
                        }

                        $duration = DB::table('attribute_values')->where('id', $payload['duration_id'])->first();
                        $durationName = strtolower($duration->attribute_value ?? '');
                        if (in_array($durationName, ['quarterly', 'half-yearly', 'quarter', 'half yearly'], true)) {
                            $monthlyDuration = DB::table('attribute_values')
                                ->where('attribute_id', $duration->attribute_id)
                                ->where('attribute_value', 'like', '%Monthly%')
                                ->first();

                            if ($monthlyDuration) {
                                $maxMonthlySortOrder = 0;
                                if (in_array($durationName, ['quarterly', 'quarter'], true)) {
                                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 3;
                                } elseif (in_array($durationName, ['half-yearly', 'half yearly'], true)) {
                                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 6;
                                }

                                $validMonthlySubDurationIds = DB::table('attribute_values')
                                    ->where('parent_id', $monthlyDuration->id)
                                    ->where('sort_order', '<=', $maxMonthlySortOrder)
                                    ->pluck('id');

                                if ($validMonthlySubDurationIds->isNotEmpty()) {
                                    $monthlyPools = DB::table('fund_pools')
                                        ->where('financial_year', $payload['financial_year'])
                                        ->where('duration_id', $monthlyDuration->id)
                                        ->whereIn('sub_duration_id', $validMonthlySubDurationIds)
                                        ->where('remaining_balance', '>', 0)
                                        ->get();

                                    foreach ($monthlyPools as $pool) {
                                        $poolsToCarryForward[] = $pool;
                                    }
                                }
                            }
                        }

                        if (!empty($poolsToCarryForward)) {
                            $poolService = app(PoolService::class);
                            foreach ($poolsToCarryForward as $prevPool) {
                                $carryAmount = (float) $prevPool->remaining_balance;
                                
                                $prevKey = [
                                    'financial_year' => $prevPool->financial_year,
                                    'duration_id' => $prevPool->duration_id,
                                    'sub_duration_id' => $prevPool->sub_duration_id,
                                    'major_component_id' => $prevPool->major_component_id,
                                    'sub_component_id' => $prevPool->sub_component_id,
                                ];
                                $poolService->updatePoolAllocation($poolService->resolvePoolKey($prevKey), -$carryAmount);

                                $currKey = [
                                    'financial_year' => $payload['financial_year'],
                                    'duration_id' => $payload['duration_id'],
                                    'sub_duration_id' => $payload['sub_duration_id'],
                                    'major_component_id' => $prevPool->major_component_id,
                                    'sub_component_id' => $prevPool->sub_component_id,
                                ];
                                $poolService->updatePoolAllocation($poolService->resolvePoolKey($currKey), $carryAmount);
                            }
                        }
                    }
                }

                // Build and insert lines
                $lines = [];
                foreach ($payload['allocation_lines'] ?? [] as $line) {
                    $amount = (float) ($line['amount'] ?? 0);
                    if ($amount <= 0) {
                        continue;
                    }

                    $lines[] = [
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'allocation_header_id' => $headerId,
                        'major_component_id' => $line['major_component_id'],
                        'sub_component_id' => $line['sub_component_id'] ?? null,
                        'amount' => $amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($lines)) {
                    DB::table('allocation_lines')->insert($lines);
                }

                // Apply impact to fund pools
                $this->applyNewAllocations($headerData, $lines);
            }

            return true;
        });
    }

    /* ─────────────────────────────────────────────────────────────
     | FUND POOL SYNC METHODS (REUSABLE & MODULAR)
     ───────────────────────────────────────────────────────────── */

    /**
     * Reverse old allocations by subtracting old amounts from matching fund pools.
     */
    private function reverseOldAllocations(object $oldHeader, \Illuminate\Support\Collection $oldLines): void
    {
        $poolService = app(PoolService::class);
        foreach ($oldLines as $line) {
            $keyData = [
                'financial_year' => $oldHeader->financial_year,
                'duration_id' => $oldHeader->duration_id,
                'sub_duration_id' => $oldHeader->sub_duration_id,
                'major_component_id' => $line->major_component_id,
                'sub_component_id' => $line->sub_component_id,
            ];

            $key = $poolService->resolvePoolKey($keyData);
            $poolService->updatePoolAllocation($key, -((float) $line->amount));
        }
    }

    /**
     * Apply new allocations by adding new amounts to matching fund pools.
     */
    private function applyNewAllocations(array $newHeader, array $newLines): void
    {
        $poolService = app(PoolService::class);
        foreach ($newLines as $line) {
            $keyData = [
                'financial_year' => $newHeader['financial_year'],
                'duration_id' => $newHeader['duration_id'],
                'sub_duration_id' => $newHeader['sub_duration_id'] ?? null,
                'major_component_id' => $line['major_component_id'],
                'sub_component_id' => $line['sub_component_id'] ?? null,
            ];

            $key = $poolService->resolvePoolKey($keyData);
            $poolService->updatePoolAllocation($key, (float) $line['amount']);
        }
    }

    /**
     * Find an existing fund pool by its unique key.
     * Keeps backward compatibility by delegating to PoolService.
     */
    private function findPool(array $key, bool $lock = false): ?object
    {
        return app(PoolService::class)->findPool($key, $lock);
    }

    /**
     * Find an existing fund pool by its unique key, or create a new one.
     * Keeps backward compatibility by delegating to PoolService.
     */
    private function findOrCreatePool(array $key, bool $lock = false): object
    {
        return app(PoolService::class)->findOrCreatePool($key, $lock);
    }

    /**
     * Recalculate remaining balance and perform optional cleanup.
     * Keeps backward compatibility by delegating to PoolService.
     */
    private function recalculateBalance(string $poolId): void
    {
        app(PoolService::class)->recalculateBalance($poolId);
    }

    /* ─────────────────────────────────────────────────────────────
     | FORM META DATA
     | Uses attribute_values with placeholder codes.
     | Replace the PLACEHOLDER_* values in config/allocation.php
     | with the real attribute codes once they are known.
     ───────────────────────────────────────────────────────────── */
    public function getFormDetails(): array
    {
        return [
            'majorcomponents' => $this->getValuesByCode(
                config('allocation.major_component_code', 'PLACEHOLDER_MAJOR_COMPONENT')
            ),
            'subcomponents' => [], // loaded on-demand via AJAX
        ];
    }

    public function getDurations(): array
    {
        return $this->getValuesByCode(
            config('allocation.duration_code', 'PLACEHOLDER_DURATION')
        );
    }

    /* ─────────────────────────────────────────────────────────────
     | ATTRIBUTE VALUE HELPERS
     ───────────────────────────────────────────────────────────── */

    /**
     * Get attribute_values by attribute CODE.
     * Returns [id => attribute_value] array (for select dropdowns).
     */
    public function getValuesByCode(string $code, ?string $parentId = null): array
    {
        $query = DB::table('attribute_values as av')
            ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
            ->where('a.code', $code)
            ->where('av.status', 1);

        if ($parentId !== null) {
            $query->where('av.parent_id', $parentId);
        } else {
            $query->whereNull('av.parent_id');
        }

        return $query->pluck('av.attribute_value', 'av.id')->toArray();
    }

    /**
     * Get attribute_values as full objects (id, attribute_value, code, parent_id)
     * Used by the AJAX cascade dropdown API.
     */
    public function getValuesByCodeForApi(string $code, ?string $parentId = null, bool $strictParentNull = false)
    {
        $query = DB::table('attribute_values as av')
            ->join('attributes as a', 'a.id', '=', 'av.attribute_id')
            ->where('a.code', $code)
            ->where('av.status', 1)
            ->select(
                'av.id',
                'av.attribute_value as name',
                'av.code',
                'av.parent_id'
            );

        if ($parentId !== null) {
            $query->where('av.parent_id', $parentId);
        } elseif ($strictParentNull) {
            $query->whereNull('av.parent_id');
        }

        return $query->orderBy('av.attribute_value')->get();
    }

    /**
     * Get MAJOR COMPONENT attribute values.
     * Cascade level 1 — no parent.
     */
    public function getMajorComponents(): \Illuminate\Support\Collection
    {
        return $this->getValuesByCodeForApi(
            config('allocation.major_component_code', 'PLACEHOLDER_MAJOR_COMPONENT'),
            null,
            true
        );
    }

    /**
     * Get SUB-COMPONENT attribute values cascaded from a Component selection.
     * Cascade level 2 — parent_id = selected component value id.
     */
    public function getSubComponents(?string $componentId = null): \Illuminate\Support\Collection
    {
        return $this->getValuesByCodeForApi(
            config('allocation.sub_component_code', 'PLACEHOLDER_SUB_COMPONENT'),
            $componentId
        );
    }

    /**
     * Get attribute_values for any arbitrary attribute (used for duration sub-values etc).
     * Callable by attribute CODE or ID.
     */
    public function getAttributeValues(string $codeOrId, ?string $parentId = null)
    {
        // Try by CODE first, then fall back to attribute_id
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
            // Treat as attribute_id (UUID or numeric id)
            $query->where('av.attribute_id', $codeOrId);
        }

        if ($parentId !== null) {
            $query->where('av.parent_id', $parentId);
        } else {
            $query->whereNull('av.parent_id');
        }

        return $query->orderBy('av.sort_order')->get();
    }

    /* ─────────────────────────────────────────────────────────────
     | HELPERS
     ───────────────────────────────────────────────────────────── */
    private function parseDate(string $date): string
    {
        // Accept dd-mm-YYYY or YYYY-mm-dd
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
        return Carbon::parse($date)->format('Y-m-d');
    }

    /* ─────────────────────────────────────────────────────────────
     | CARRY FORWARD BALANCE
     ───────────────────────────────────────────────────────────── */
    public function getPreviousBalance(?string $financialYear, ?string $durationId, ?string $subDurationId): float
    {
        if (empty($financialYear) || empty($durationId) || empty($subDurationId)) {
            return 0.00;
        }

        $balance = 0.00;

        // Get the current sub duration
        $currentSubDuration = DB::table('attribute_values')->where('id', $subDurationId)->first();
        if ($currentSubDuration) {
            // Find the previous sub durations based on sort_order
            $previousSubDurationIds = DB::table('attribute_values')
                ->where('parent_id', $currentSubDuration->parent_id)
                ->where('attribute_id', $currentSubDuration->attribute_id)
                ->where('sort_order', '<', $currentSubDuration->sort_order)
                ->pluck('id');

            if ($previousSubDurationIds->isNotEmpty()) {
                // Sum all remaining balances in fund pools for the previous sub durations
                $balance += (float) DB::table('fund_pools')
                    ->where('financial_year', $financialYear)
                    ->where('duration_id', $durationId)
                    ->whereIn('sub_duration_id', $previousSubDurationIds)
                    ->sum('remaining_balance');
            }
        }

        // Also check if there are monthly balances when selecting Quarterly or Half-Yearly
        $duration = DB::table('attribute_values')->where('id', $durationId)->first();
        $durationName = strtolower($duration->attribute_value ?? '');
        if (in_array($durationName, ['quarterly', 'half-yearly', 'quarter', 'half yearly'], true) && $currentSubDuration) {
            $monthlyDuration = DB::table('attribute_values')
                ->where('attribute_id', $duration->attribute_id)
                ->where('attribute_value', 'like', '%Monthly%')
                ->first();

            if ($monthlyDuration) {
                $maxMonthlySortOrder = 0;
                if (in_array($durationName, ['quarterly', 'quarter'], true)) {
                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 3;
                } elseif (in_array($durationName, ['half-yearly', 'half yearly'], true)) {
                    $maxMonthlySortOrder = $currentSubDuration->sort_order * 6;
                }

                $validMonthlySubDurationIds = DB::table('attribute_values')
                    ->where('parent_id', $monthlyDuration->id)
                    ->where('sort_order', '<=', $maxMonthlySortOrder)
                    ->pluck('id');

                if ($validMonthlySubDurationIds->isNotEmpty()) {
                    $balance += (float) DB::table('fund_pools')
                        ->where('financial_year', $financialYear)
                        ->where('duration_id', $monthlyDuration->id)
                        ->whereIn('sub_duration_id', $validMonthlySubDurationIds)
                        ->sum('remaining_balance');
                }
            }
        }

        return $balance;
    }
}