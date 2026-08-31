<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Web\Allocation\PoolService;
use App\Domain\FundCarryForward\FundCarryForward;
use App\Domain\FundCarryForward\FundCarryForwardDetail;
use App\Domain\FundCarryForward\FundCarryForwardLog;
use Carbon\Carbon;

class StoreFundAllocationAction
{
    public function __construct(private FundAllocationService $fundAllocationService) {}

    public function execute(FundAllocationDTO $dto, ?string $id = null): FundAllocation
    {
        // dd($dto);
        return DB::transaction(function () use ($dto, $id) {
            $poolService = app(PoolService::class);

            // Fetch target period's previous balances before transaction makes changes
            $prevUnallocated = $this->fundAllocationService->getUnallocatedRemainderForPeriod(
                $dto->financialYear,
                $dto->durationId,
                $dto->subDurationId
            );

            $prevComponentBalances = [];
            $sourcePeriods = collect();
            $componentsToLog = [];
            if ($dto->applyCarryForward && !empty($dto->subDurationId)) {
                $sourcePeriods = $this->fundAllocationService->resolveCarryForwardSourcePeriods(
                    $dto->financialYear,
                    $dto->durationId,
                    $dto->subDurationId
                );
            }

            $allComponentKeys = collect($dto->allocationLines)
                ->map(fn($line) => $line['major_component_id'] . '_' . ($line['sub_component_id'] ?? 'NULL'));

            foreach ($sourcePeriods as $period) {
                $undistributedByComponent = $this->fundAllocationService->getUndistributedAmountsByComponent(
                    $dto->financialYear,
                    $period['duration_id'],
                    $period['sub_duration_id']
                );
                foreach (array_keys($undistributedByComponent) as $compKey) {
                    $allComponentKeys->push($compKey);
                }
            }

            $allComponentKeys = $allComponentKeys->unique();

            foreach ($allComponentKeys as $compKey) {
                $parts = explode('_', $compKey);
                $targetKey = $poolService->resolvePoolKey([
                    'financial_year'     => $dto->financialYear,
                    'duration_id'        => $dto->durationId,
                    'sub_duration_id'    => $dto->subDurationId,
                    'major_component_id' => $parts[0],
                    'sub_component_id'   => $parts[1] !== 'NULL' ? $parts[1] : null,
                ]);
                $targetPool = $poolService->findPool($targetKey);
                $prevComponentBalances[$compKey] = $targetPool ? (float)$targetPool->remaining_balance : 0.0;
            }



            $headerData = [
                'financial_year'        => $dto->financialYear,
                'duration_id'           => $dto->durationId,
                'sub_duration_id'       => $dto->subDurationId,
                'sanction_order_number' => $dto->sanctionOrderNumber,
                'sanction_order_date'   => $this->parseDate($dto->sanctionOrderDate),
                'document_path'         => $dto->documentPath,
                'remarks'               => $dto->remarks,
                'total_available_amount' => $dto->totalAvailableAmount,
                'fresh_allocation_amount' => $dto->freshAllocationAmount,
                'total_allocated_amount'  => $dto->totalAmount,
            ];

            $carryForwardMap = [];
            $carryForwardMapForHistory = [];
            $unallocatedCarryForward = 0.0;
            $carryForwardSourcePeriods = collect();
            $totalCarriedForward = 0.0;

            // Whether this transaction MERGES its amounts into an already-existing Fund
            // Allocation record for the exact same Financial Year + Duration + Sub
            // Duration (recalculating that record's totals/mappings in place) instead of
            // inserting a sibling duplicate header row. Distinct from Edit mode ($id
            // given, below): that's the user explicitly editing ONE specific record;
            // this is "Add Allocation" landing on a period that already has a record.
            $isMergeIntoExisting = false;
            $historyType = 'create';

            if ($id) {
                // Edit mode
                $fundAllocation = FundAllocation::findOrFail($id);

                $this->assertReductionsDoNotUnderrunConsumedAmounts($fundAllocation, $dto);

                // Reverse old impact from fund pools
                foreach ($fundAllocation->componentMappings as $mapping) {
                    $keyData = [
                        'financial_year'     => $fundAllocation->financial_year,
                        'duration_id'        => $fundAllocation->duration_id,
                        'sub_duration_id'    => $fundAllocation->sub_duration_id,
                        'major_component_id' => $mapping->major_component_id,
                        'sub_component_id'   => $mapping->sub_component_id,
                    ];

                    $key = $poolService->resolvePoolKey($keyData);

                    $poolService->updatePoolAllocation($key, - ((float) $mapping->amount));
                }

                // The header's total (fresh sanction, possibly including whatever was
                // carried forward into it originally) round-trips via fresh_allocation_amount
                // unless the user re-ran the Fresh Allocation modal with a new figure.
                $headerData['total_amount_allocated'] = $dto->freshAllocationAmount;
                $headerData['fresh_allocation_amount'] = $dto->freshAllocationAmount;
                $headerData['total_allocated_amount'] = $dto->totalAmount;
                $headerData['updated_by'] = AuthId();
                $fundAllocation->update($headerData);

                // Recreate lines/mappings
                $fundAllocation->componentMappings()->delete();
                $historyType = 'edit';
            } else {
                // Create mode: recalculate an EXISTING record for this exact period
                // instead of creating a duplicate sibling row, if one already exists.
                $existingAllocation = FundAllocation::where('financial_year', $dto->financialYear)
                    ->where('duration_id', $dto->durationId)
                    ->when(
                        !empty($dto->subDurationId),
                        fn($q) => $q->where('sub_duration_id', $dto->subDurationId),
                        fn($q) => $q->whereNull('sub_duration_id')
                    )
                    ->first();

                // --- AUTOMATIC CARRY FORWARD LOGIC ---
                if ($dto->applyCarryForward && !empty($dto->subDurationId)) {
                    // Carry forward must only ever land on the component(s) the user is
                    // actually allocating in this submission -- never silently create
                    // lines for other components that happen to have leftover balance.
                    $selectedCompKeys = [];
                    foreach ($dto->allocationLines as $line) {
                        if ((float)($line['amount'] ?? 0) > 0) {
                            $selectedCompKeys[$line['major_component_id'] . '_' . ($line['sub_component_id'] ?? 'NULL')] = true;
                        }
                    }

                    $carryForwardSourcePeriods = $this->fundAllocationService->resolveCarryForwardSourcePeriods(
                        $dto->financialYear,
                        $dto->durationId,
                        $dto->subDurationId
                    );

                    foreach ($carryForwardSourcePeriods as $period) {
                        // Per-component undistributed money in the source period (allocated
                        // but not yet paid out). Computed from fund_allocation_component_mappings
                        // / fund_distributions rather than fund_pools, since this app can run in
                        // "merged" pooling mode where one pool is shared across a component's
                        // whole financial year and can't answer "how much of THIS period is
                        // still undistributed" -- see getUndistributedAmountsByComponent().
                        $undistributedByComponent = $this->fundAllocationService->getUndistributedAmountsByComponent(
                            $dto->financialYear,
                            $period['duration_id'],
                            $period['sub_duration_id']
                        );

                        foreach ($undistributedByComponent as $compKey => $carryAmount) {
                            if ($carryAmount <= 0) {
                                continue;
                            }

                            $parts = explode('_', $compKey);
                            $majorComp = $parts[0];
                            $subComp = $parts[1] !== 'NULL' ? $parts[1] : null;

                            // Collect component remaining amount to log in fund_carry_forward_logs
                            $componentsToLog[] = [
                                'from_period'        => $period,
                                'major_component_id' => $majorComp,
                                'sub_component_id'   => $subComp,
                                'amount'             => $carryAmount,
                                'comp_key'           => $compKey,
                            ];

                            // The user requested that NO extra amount should be added from carry forward,
                            // utilization mapping, or previous component data to the new Allocated Fund.
                            // Therefore, all unspent amounts from components in the previous period
                            // simply revert to the Unallocated carry-forward pool for this new period.
                            $unallocatedCarryForward += $carryAmount;

                            // Release the money from the source period's pool key!
                            $prevKey = $poolService->resolvePoolKey([
                                'financial_year' => $dto->financialYear,
                                'duration_id' => $period['duration_id'],
                                'sub_duration_id' => $period['sub_duration_id'],
                                'major_component_id' => $majorComp,
                                'sub_component_id' => $subComp,
                            ]);
                            $poolService->updatePoolAllocation($prevKey, -$carryAmount);
                        }

                        // Header-level remainder that was never mapped to any component
                        // in the source period -- not tied to a component, so it simply
                        // enlarges this period's own unmapped total instead of becoming a
                        // synthetic component line.
                        $unallocatedCarryForward += $this->fundAllocationService->getUnallocatedRemainderForPeriod(
                            $dto->financialYear,
                            $period['duration_id'],
                            $period['sub_duration_id']
                        );
                    }
                }

                $totalCarriedForward = array_sum($carryForwardMap) + $unallocatedCarryForward;
                // $carryForwardMap is mutated below (entries are unset() as they get
                // merged into matching user-typed lines) -- snapshot it now so the
                // history log still reflects everything that was actually carried,
                // not just what ended up as a standalone synthetic line.
                $carryForwardMapForHistory = $carryForwardMap;

                if ($existingAllocation) {
                    $isMergeIntoExisting = true;
                    $historyType = 'topup';
                    $headerData['total_amount_allocated'] = (float) $existingAllocation->total_amount_allocated
                        + $dto->freshAllocationAmount + $totalCarriedForward;
                    $headerData['fresh_allocation_amount'] = (float) $existingAllocation->fresh_allocation_amount
                        + $dto->freshAllocationAmount;
                    $headerData['total_allocated_amount'] = (float) $existingAllocation->total_allocated_amount
                        + $dto->totalAmount;
                    $headerData['updated_by'] = AuthId();
                    $existingAllocation->update($headerData);
                    $fundAllocation = $existingAllocation;
                } else {
                    $headerData['id'] = (string) Str::uuid();
                    $headerData['created_by'] = AuthId();
                    $headerData['total_amount_allocated'] = $dto->freshAllocationAmount + $totalCarriedForward;
                    $fundAllocation = FundAllocation::create($headerData);
                }
            }

            // Create new mappings and apply new impact
            // We need to ensure that any carry forwarded component without a direct line also gets a line!
            $finalLines = [];
            foreach ($dto->allocationLines as $line) {
                $compKey = $line['major_component_id'] . '_' . ($line['sub_component_id'] ?? 'NULL');
                $amount = (float) ($line['amount'] ?? 0);

                if (isset($carryForwardMap[$compKey])) {
                    $amount += $carryForwardMap[$compKey];
                    unset($carryForwardMap[$compKey]);
                }

                if ($amount > 0) {
                    $finalLines[] = [
                        'major_component_id' => $line['major_component_id'],
                        'sub_component_id'   => $line['sub_component_id'] ?? null,
                        'amount'             => $amount,
                    ];
                }
            }

            // Note: carry forward is pre-filtered above to only components already
            // present in $dto->allocationLines, so every entry in $carryForwardMap is
            // guaranteed to have been merged into a matching line already -- carry
            // forward must never silently create a line for a component the user did
            // not select.

            foreach ($finalLines as $line) {
                $amount = (float) $line['amount'];

                // Merging into an existing period recalculates that component's already-
                // mapped amount in place (adds to it) rather than inserting a second
                // mapping row for the same component -- true Edit mode already deleted
                // all mappings above, and a brand-new period has none yet, so both of
                // those cases always create fresh.
                $existingMapping = $isMergeIntoExisting
                    ? $fundAllocation->componentMappings()
                    ->where('major_component_id', $line['major_component_id'])
                    ->when(
                        !empty($line['sub_component_id']),
                        fn($q) => $q->where('sub_component_id', $line['sub_component_id']),
                        fn($q) => $q->whereNull('sub_component_id')
                    )
                    ->first()
                    : null;

                if ($existingMapping) {
                    $existingMapping->update(['amount' => (float) $existingMapping->amount + $amount]);
                } else {
                    $fundAllocation->componentMappings()->create([
                        'id'                 => (string) Str::uuid(),
                        'major_component_id' => $line['major_component_id'],
                        'sub_component_id'   => $line['sub_component_id'] ?? null,
                        'amount'             => $amount,
                    ]);
                }

                // Apply new impact to fund pools (this now includes both carry forward + fresh allocation)
                $keyData = [
                    'financial_year'     => $dto->financialYear,
                    'duration_id'        => $dto->durationId,
                    'sub_duration_id'    => $dto->subDurationId,
                    'major_component_id' => $line['major_component_id'],
                    'sub_component_id'   => $line['sub_component_id'] ?? null,
                ];
                $key = $poolService->resolvePoolKey($keyData);
                $poolService->updatePoolAllocation($key, $amount);
            }

            $this->recordCarryForwardHistory(
                $dto,
                $carryForwardSourcePeriods,
                $carryForwardMapForHistory,
                $unallocatedCarryForward,
                $prevUnallocated,
                $prevComponentBalances,
                $componentsToLog
            );

            $this->recordAllocationHistory($dto, $fundAllocation, $historyType, $finalLines, $totalCarriedForward);

            return $fundAllocation->load('componentMappings');
        });
    }

    /**
     * Log every save against a Fund Allocation -- initial creation, a later top-up that
     * merges into the same exact-period record, or an explicit edit -- as its own
     * immutable history entry. Since recalculating in place (see execute() above) means
     * a period's whole story can no longer be read off multiple fund_allocations rows,
     * this is what lets the View page show the complete transaction history instead of
     * losing everything but the current totals.
     */
    private function recordAllocationHistory(
        FundAllocationDTO $dto,
        FundAllocation $fundAllocation,
        string $type,
        array $finalLines,
        float $carriedForwardAmount
    ): void {
        FundAllocationHistory::create([
            'id'                            => (string) Str::uuid(),
            'fund_allocation_id'            => $fundAllocation->id,
            'financial_year'                => $dto->financialYear,
            'duration_id'                   => $dto->durationId,
            'sub_duration_id'               => $dto->subDurationId,
            'type'                          => $type,
            'fresh_allocation_amount'       => $dto->freshAllocationAmount,
            'carried_forward_amount'        => $carriedForwardAmount,
            'component_lines'               => array_map(fn($line) => [
                'major_component_id' => $line['major_component_id'],
                'sub_component_id'   => $line['sub_component_id'] ?? null,
                'amount'             => (float) $line['amount'],
            ], $finalLines),
            'total_amount_allocated_after'  => (float) $fundAllocation->total_amount_allocated,
            'total_available_amount_after'  => (float) $fundAllocation->total_available_amount,
            'sanction_order_number'         => $dto->sanctionOrderNumber,
            'sanction_order_date'           => $this->parseDate($dto->sanctionOrderDate),
            'document_path'                 => $dto->documentPath,
            'remarks'                       => $dto->remarks,
            'created_by'                    => AuthId(),
        ]);
    }

    /**
     * Guard against an Edit reducing/removing a component's allocated amount below what
     * has already been distributed or transferred away via Component Utilization Mapping
     * for that component in this period -- doing so would leave fund_pools with a
     * negative/nonsensical remaining balance everywhere that pool is read from.
     */
    private function assertReductionsDoNotUnderrunConsumedAmounts(FundAllocation $fundAllocation, FundAllocationDTO $dto): void
    {
        $poolService = app(PoolService::class);

        $newAmountByComponent = [];
        foreach ($dto->allocationLines as $line) {
            $compKey = $line['major_component_id'] . '_' . ($line['sub_component_id'] ?? 'NULL');
            $newAmountByComponent[$compKey] = ($newAmountByComponent[$compKey] ?? 0.0) + (float) ($line['amount'] ?? 0);
        }

        foreach ($fundAllocation->componentMappings as $mapping) {
            $compKey = $mapping->major_component_id . '_' . ($mapping->sub_component_id ?? 'NULL');
            $newAmount = $newAmountByComponent[$compKey] ?? 0.0;

            $key = $poolService->resolvePoolKey([
                'financial_year'     => $fundAllocation->financial_year,
                'duration_id'        => $fundAllocation->duration_id,
                'sub_duration_id'    => $fundAllocation->sub_duration_id,
                'major_component_id' => $mapping->major_component_id,
                'sub_component_id'   => $mapping->sub_component_id,
            ]);
            $pool = $poolService->findPool($key);
            $consumed = $pool ? (float) $pool->total_distributed_amount : 0.0;

            if ($newAmount < $consumed) {
                throw new \RuntimeException(
                    "Cannot reduce the allocated amount for this component below ₹{$consumed} — that much has already been distributed against it."
                );
            }
        }
    }

    /**
     * Log an automatic carry-forward sweep as Fund Carry Forward history so it shows up
     * in the (now routed, read-only) carry-forward history list/view.
     */
    private function recordCarryForwardHistory(
        FundAllocationDTO $dto,
        \Illuminate\Support\Collection $sourcePeriods,
        array $carryForwardMap,
        float $unallocatedCarryForward,
        float $prevUnallocated,
        array $prevComponentBalances,
        array $componentsToLog
    ): void {
        $totalCarried = array_sum($carryForwardMap) + $unallocatedCarryForward;

        if ($totalCarried <= 0 || $sourcePeriods->isEmpty()) {
            return;
        }

        $earliestSource = $sourcePeriods->first();

        $carryForward = FundCarryForward::create([
            'id'                   => (string) Str::uuid(),
            'financial_year'       => $dto->financialYear,
            'from_duration_id'     => $earliestSource['duration_id'],
            'from_sub_duration_id' => $earliestSource['sub_duration_id'],
            'to_duration_id'       => $dto->durationId,
            'to_sub_duration_id'   => $dto->subDurationId,
            'carry_forward_date'   => now()->toDateString(),
            'total_amount'         => $totalCarried,
            'remarks'              => 'Automatically carried forward on new allocation creation.',
            'status'               => 'Completed',
            'created_by'           => AuthId(),
            'updated_by'           => AuthId(),
        ]);

        foreach ($carryForwardMap as $compKey => $carryAmt) {
            if ($carryAmt <= 0) {
                continue;
            }
            $parts = explode('_', $compKey);
            $majorComp = $parts[0];
            $subComp = $parts[1] !== 'NULL' ? $parts[1] : null;

            FundCarryForwardDetail::create([
                'id'                      => (string) Str::uuid(),
                'carry_forward_id'        => $carryForward->id,
                'major_component_id'      => $majorComp,
                'sub_component_id'        => $subComp,
                'opening_balance'         => $carryAmt,
                'carried_forward_amount'  => $carryAmt,
            ]);

            $prevBal = $prevComponentBalances[$compKey] ?? 0.0;
            FundCarryForwardLog::create([
                'id'                  => (string) Str::uuid(),
                'carry_forward_id'    => $carryForward->id,
                'financial_year'      => $dto->financialYear,
                'from_duration_id'    => $earliestSource['sub_duration_id'] ?: $earliestSource['duration_id'],
                'to_duration_id'      => $dto->subDurationId ?: $dto->durationId,
                'major_component_id'  => $majorComp,
                'sub_component_id'    => $subComp,
                'previous_balance'    => $prevBal,
                'carried_amount'      => $carryAmt,
                'new_opening_balance' => $prevBal + $carryAmt,
                'action'              => 'CREATE',
                'remarks'             => 'Automatically carried forward component balance on new allocation creation.',
                'created_by'          => AuthId(),
            ]);
        }

        foreach ($componentsToLog as $item) {
            $fromPeriod = $item['from_period'];
            $carryAmt = (float)$item['amount'];
            $majorComp = $item['major_component_id'];
            $subComp = $item['sub_component_id'];

            FundCarryForwardLog::create([
                'id'                  => (string) Str::uuid(),
                'carry_forward_id'    => $carryForward->id,
                'financial_year'      => $dto->financialYear,
                'from_duration_id'    => $fromPeriod['sub_duration_id'] ?: $fromPeriod['duration_id'],
                'to_duration_id'      => $dto->subDurationId ?: $dto->durationId,
                'major_component_id'  => $majorComp,
                'sub_component_id'    => $subComp,
                'previous_balance'    => $carryAmt,
                'carried_amount'      => $carryAmt,
                'new_opening_balance' => 0.0,
                'action'              => 'CREATE',
                'remarks'             => 'Automatically carried forward component balance on new allocation creation.',
                'created_by'          => AuthId(),
            ]);
        }

        if ($unallocatedCarryForward > 0) {
            FundCarryForwardDetail::create([
                'id'                     => (string) Str::uuid(),
                'carry_forward_id'       => $carryForward->id,
                'major_component_id'     => FundCarryForwardDetail::UNALLOCATED_COMPONENT,
                'sub_component_id'       => null,
                'opening_balance'        => $unallocatedCarryForward,
                'carried_forward_amount' => $unallocatedCarryForward,
                'remarks'                => 'Unallocated (unmapped) remainder from the source period.',
            ]);

            FundCarryForwardLog::create([
                'id'                  => (string) Str::uuid(),
                'carry_forward_id'    => $carryForward->id,
                'financial_year'      => $dto->financialYear,
                'from_duration_id'    => $earliestSource['sub_duration_id'] ?: $earliestSource['duration_id'],
                'to_duration_id'      => $dto->subDurationId ?: $dto->durationId,
                'major_component_id'  => FundCarryForwardDetail::UNALLOCATED_COMPONENT,
                'sub_component_id'    => null,
                'previous_balance'    => $prevUnallocated,
                'carried_amount'      => $unallocatedCarryForward,
                'new_opening_balance' => $prevUnallocated + $unallocatedCarryForward,
                'action'              => 'CREATE',
                'remarks'             => 'Unallocated remainder carried forward on new allocation creation.',
                'created_by'          => AuthId(),
            ]);
        }
    }

    private function parseDate(string $date): string
    {
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            return Carbon::createFromFormat('d-m-Y', $date)->format('Y-m-d');
        }
        return Carbon::parse($date)->format('Y-m-d');
    }
}
