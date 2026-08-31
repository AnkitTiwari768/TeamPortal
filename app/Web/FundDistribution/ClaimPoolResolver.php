<?php

declare(strict_types=1);

namespace App\Web\FundDistribution;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Web\Allocation\PoolService;
use Illuminate\Support\Facades\Log;

/**
 * ClaimPoolResolver
 *
 * Specialized discovery engine to locate correct deduction pools.
 * Dynamically adapts between STRICT and MERGED modes, and supports
 * multi-pool selection for "Waterfall" deduction strategies.
 */
class ClaimPoolResolver
{
    protected PoolService $poolService;

    public function __construct(PoolService $poolService)
    {
        $this->poolService = $poolService;
    }

    /**
     * High-level resolution entrypoint.
     * Automatically branches logic by configuration state and input dataset.
     *
     * @param array $criteria Search payload (fy, components, durations)
     * @return Collection Array of hydrated pool database records.
     */
    public function resolve(array $criteria): Collection
    {
        $poolingMode = 2; //(int) config('allocation.pooling_mode', 1);

        Log::debug("Resolution engine started.", [
            'mode' => $poolingMode,
            'has_duration' => !empty($criteria['duration_id'])
        ]);

        // CASE 3: Merged Mode (Mode 2)
        // Ignores specific durations, targets centralized yearly component pool.
        if ($poolingMode === 2) {
            return $this->resolveMergedModePool($criteria);
        }

        // CASE 1: Strict Mode WITH exact duration information.
        // Targets single unique record match.
        if (!empty($criteria['duration_id'])) {
            return $this->resolveStrictModeExactPool($criteria);
        }

        // CASE 2: Strict Mode WITHOUT specific duration information.
        // Dispatches dynamic lookup across ALL available durations for sequential Waterfall.
        return $this->resolveStrictModeWaterfallPools($criteria);
    }

    /**
     * Strategy for Merged Pooling Mode.
     */
    protected function resolveMergedModePool(array $criteria): Collection
    {
        // Delegate key building to existing PoolService for consistency (injects dummy uuid)
        $key = $this->poolService->resolvePoolKey($criteria);

        // For consolidated pools, existence is theoretically mandatory for safe logic, 
        // but systems often auto-initialize them. findOrCreate guarantees execution safety.
        $pool = $this->poolService->findOrCreatePool($key);

        return collect([$pool]);
    }

    /**
     * Strategy for Strict Mode (Exact Record).
     */
    protected function resolveStrictModeExactPool(array $criteria): Collection
    {
        $fy = $criteria['financial_year'];
        $majorId = $criteria['major_component_id'];
        $subId = $criteria['sub_component_id'] ?? null;

        // Fetch all possible candidate pools for the component and financial year
        $query = DB::table('fund_pools')
            ->where('financial_year', $fy)
            ->where('major_component_id', $majorId);

        if (is_null($subId)) {
            $query->whereNull('sub_component_id');
        } else {
            $query->where('sub_component_id', $subId);
        }

        $allCandidatePools = $query->get();

        // Get month range for the requested criteria
        $requestedMonths = $this->getMonthsCovered($criteria['duration_id'] ?? null, $criteria['sub_duration_id'] ?? null);

        // Filter candidates that have overlapping month coverage
        $filteredPools = $allCandidatePools->filter(function ($pool) use ($requestedMonths) {
            $poolMonths = $this->getMonthsCovered($pool->duration_id, $pool->sub_duration_id);
            return !empty(array_intersect($requestedMonths, $poolMonths));
        });

        if ($filteredPools->isEmpty()) {
            // Missing allocation must not block the caller (e.g. marking a workshop
            // Completed) -- return no candidates instead of throwing, same as the Add
            // flow already tolerates.
            Log::warning("No overlapping pools found in strict mode lookup.", ['criteria' => $criteria]);
            return collect();
        }

        // Sort pools to prioritize positive remaining balance, then oldest created_at first.
        $sortedPools = $filteredPools->sortBy([
            ['remaining_balance', 'desc'],
            ['created_at', 'asc'],
        ]);

        return $sortedPools;
    }

    /**
     * Resolve months covered by a given duration and sub-duration.
     *
     * @param string|null $durationId
     * @param string|null $subDurationId
     * @return array Array of month numbers (1-12)
     */
    protected function getMonthsCovered(?string $durationId, ?string $subDurationId): array
    {
        if (empty($durationId)) {
            return range(1, 12);
        }

        // Fetch all attribute values to resolve metadata dynamically without hardcoding IDs
        $attributes = DB::table('attribute_values')->get();
        $attrMap = $attributes->keyBy('id');

        $duration = $attrMap->get($durationId);
        if (!$duration) {
            return range(1, 12);
        }

        $durationCode = strtolower($duration->code ?? $duration->attribute_value ?? '');

        // If no sub-duration is specified
        if (empty($subDurationId)) {
            return range(1, 12);
        }

        $subDuration = $attrMap->get($subDurationId);
        if (!$subDuration) {
            return range(1, 12);
        }

        $subCode = strtolower($subDuration->code ?? $subDuration->attribute_value ?? '');

        if (str_contains($durationCode, 'yearly')) {
            return range(1, 12);
        }

        if (str_contains($durationCode, 'half')) {
            if (str_contains($subCode, 'first') || str_contains($subCode, '1st')) {
                return [4, 5, 6, 7, 8, 9]; // April to September
            }
            if (str_contains($subCode, 'second') || str_contains($subCode, '2nd')) {
                return [10, 11, 12, 1, 2, 3]; // October to March
            }
        }

        if (str_contains($durationCode, 'quarter')) {
            if (str_contains($subCode, 'first') || str_contains($subCode, '1st') || str_contains($subCode, 'q1')) {
                return [4, 5, 6]; // April to June
            }
            if (str_contains($subCode, 'second') || str_contains($subCode, '2nd') || str_contains($subCode, 'q2')) {
                return [7, 8, 9]; // July to September
            }
            if (str_contains($subCode, 'third') || str_contains($subCode, '3rd') || str_contains($subCode, 'q3')) {
                return [10, 11, 12]; // October to December
            }
            if (str_contains($subCode, 'fourth') || str_contains($subCode, '4th') || str_contains($subCode, 'q4')) {
                return [1, 2, 3]; // January to March
            }
        }

        if (str_contains($durationCode, 'month')) {
            if (str_contains($subCode, 'january') || str_contains($subCode, 'jan')) return [1];
            if (str_contains($subCode, 'february') || str_contains($subCode, 'feb')) return [2];
            if (str_contains($subCode, 'march') || str_contains($subCode, 'mar')) return [3];
            if (str_contains($subCode, 'april') || str_contains($subCode, 'apr')) return [4];
            if (str_contains($subCode, 'may')) return [5];
            if (str_contains($subCode, 'june') || str_contains($subCode, 'jun')) return [6];
            if (str_contains($subCode, 'july') || str_contains($subCode, 'jul')) return [7];
            if (str_contains($subCode, 'august') || str_contains($subCode, 'aug')) return [8];
            if (str_contains($subCode, 'september') || str_contains($subCode, 'sep')) return [9];
            if (str_contains($subCode, 'october') || str_contains($subCode, 'oct')) return [10];
            if (str_contains($subCode, 'november') || str_contains($subCode, 'nov')) return [11];
            if (str_contains($subCode, 'december') || str_contains($subCode, 'dec')) return [12];
        }

        return range(1, 12);
    }

    /**
     * Strategy for Strict Mode (Multi-pool Waterfall).
     */
    protected function resolveStrictModeWaterfallPools(array $criteria): Collection
    {
        $fy = $criteria['financial_year'];
        $majorId = $criteria['major_component_id'];
        $subId = $criteria['sub_component_id'] ?? null;

        // Prepare robust consolidated query fetcher
        $query = DB::table('fund_pools')
            ->where('financial_year', $fy)
            ->where('major_component_id', $majorId);

        if (is_null($subId)) {
            $query->whereNull('sub_component_id');
        } else {
            $query->where('sub_component_id', $subId);
        }

        /**
         * WATERFALL SORT ORDERING REQUIREMENT:
         * 1. Prioritize pools with positive funds remaining.
         * 2. Prioritize the OLDEST allocations first (by created_at).
         */
        $pools = $query
            ->orderByRaw("CASE WHEN remaining_balance > 0 THEN 1 ELSE 0 END DESC")
            ->orderBy('created_at', 'ASC')
            ->get();

        if ($pools->isEmpty()) {
            // Missing allocation must not block the caller -- return no candidates
            // instead of throwing, same as the Add flow already tolerates.
            Log::warning("Waterfall logic stalled: No applicable pool targets encountered.", [
                'financial_year' => $fy,
                'major' => $majorId
            ]);
            return collect();
        }

        Log::info("Waterfall plan generated with " . $pools->count() . " eligible targets.");

        return $pools;
    }
}
