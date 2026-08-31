<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Illuminate\Support\Facades\DB;

class FundAllocationService
{
    public function getAttributeValues(string $codeOrId, ?string $parentId = null)
    {
        $byCode = DB::table('attributes')->where('code', $codeOrId)->first();

        if (!$byCode && in_array($codeOrId, ['sub-components', 'sub_component', 'PLACEHOLDER_SUB_COMPONENT'], true)) {
            $byCode = DB::table('attributes')->where('code', 'major-components')->first();
        }

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


    public function getFundAllocationEditDetails(string $id): array
    {
        $fundAllocation = $this->getFundAllocation($id);

        return [
            'fundAllocation' => $fundAllocation->toArray(),
            'componentMappings' => $this->formatComponentMappings($fundAllocation),
            'documentUrl' => $this->getDocumentUrl($fundAllocation->document_path),
        ];
    }

    public function getFundAllocationViewDetails(string $id): array
    {
        $fundAllocation = $this->getFundAllocation($id);
        $history = FundAllocationHistory::where('fund_allocation_id', $id)
            ->orderBy('created_at')
            ->get();

        // Collect all Attribute IDs
        $attributeIds = collect([
            $fundAllocation->duration_id,
            $fundAllocation->sub_duration_id,
        ])
            ->merge($fundAllocation->componentMappings->pluck('major_component_id'))
            ->merge($fundAllocation->componentMappings->pluck('sub_component_id'))
            ->merge($history->flatMap(fn($entry) => collect($entry->component_lines ?? [])->flatMap(
                fn($line) => [$line['major_component_id'] ?? null, $line['sub_component_id'] ?? null]
            )))
            ->filter()
            ->unique();

        // Fetch all names in one query
        $attributeValues = DB::table('attribute_values')
            ->whereIn('id', $attributeIds)
            ->pluck('attribute_value', 'id');

        $componentMappings = $fundAllocation->componentMappings->map(function ($mapping) use ($attributeValues, $fundAllocation) {
            $balance = $this->getComponentBalance(
                $fundAllocation->financial_year,
                $fundAllocation->duration_id,
                $fundAllocation->sub_duration_id,
                $mapping->major_component_id,
                $mapping->sub_component_id
            );

            return [
                'id' => $mapping->id,
                'major_component_id' => $mapping->major_component_id,
                'major_component_name' => $attributeValues[$mapping->major_component_id] ?? '-',
                'sub_component_id' => $mapping->sub_component_id,
                'sub_component_name' => $attributeValues[$mapping->sub_component_id] ?? '-',
                'amount' => $mapping->amount,
                'distributed_to_other_component' => $this->getDistributedAwayAmount(
                    $fundAllocation->financial_year,
                    $fundAllocation->duration_id,
                    $fundAllocation->sub_duration_id,
                    $mapping->major_component_id,
                    $mapping->sub_component_id
                ),
                'remaining_amount' => $balance['remaining_balance'] ?? 0.0,
            ];
        });

        $userIds = $history->pluck('created_by')->filter()->unique();
        $userNames = DB::table('users')->whereIn('id', $userIds)->get()->mapWithKeys(function ($user) {
            $fullName = trim(preg_replace('/\s+/', ' ', "{$user->first_name} {$user->middle_name} {$user->last_name}"));
            return [$user->id => $fullName];
        });

        // Every save against this Fund Allocation (initial creation, later top-ups that
        // recalculated it in place, or explicit edits) as its own transaction -- since
        // recalculating in place means the current row no longer tells that whole story
        // by itself, this is what surfaces the complete history on the View page.
        $allocationHistory = $history->map(function ($entry) use ($attributeValues, $userNames) {
            return [
                'id' => $entry->id,
                'type' => $entry->type,
                'fresh_allocation_amount' => (float) $entry->fresh_allocation_amount,
                'carried_forward_amount' => (float) $entry->carried_forward_amount,
                'component_lines' => collect($entry->component_lines ?? [])->map(fn($line) => [
                    'major_component_name' => $attributeValues[$line['major_component_id'] ?? null] ?? '-',
                    'sub_component_name' => $attributeValues[$line['sub_component_id'] ?? null] ?? '-',
                    'amount' => (float) ($line['amount'] ?? 0),
                ])->values(),
                'total_amount_allocated_after' => (float) $entry->total_amount_allocated_after,
                'total_available_amount_after' => (float) $entry->total_available_amount_after,
                'sanction_order_number' => $entry->sanction_order_number,
                'sanction_order_date' => $entry->sanction_order_date ? $entry->sanction_order_date->format('d-m-Y') : '-',
                'remarks' => $entry->remarks,
                'created_by_name' => $userNames[$entry->created_by] ?? '-',
                'created_at' => $entry->created_at->format('d-m-Y H:i'),
            ];
        })->values();

        // "Total Available Amount" is a period-wide figure (this period's whole sanctioned
        // total minus what's been assigned to components, minus whatever has already been
        // swept forward by a prior carry-forward sweep), not this one header row's own
        // stale total_available_amount snapshot from creation time -- reuses the same
        // getUnallocatedRemainderForPeriod() the Create page's Opening Balance and
        // ListFundAllocationAction's List page both use, so all three agree, including
        // after an auto carry-forward sweep has moved some of this period's remainder
        // into a later period.
        $periodAvailableAmount = $this->getUnallocatedRemainderForPeriod(
            $fundAllocation->financial_year,
            $fundAllocation->duration_id,
            $fundAllocation->sub_duration_id
        );

        return [
            'fundAllocation' => array_merge(
                $fundAllocation->toArray(),
                [
                    'duration_name' => $attributeValues[$fundAllocation->duration_id] ?? '-',
                    'sub_duration_name' => $attributeValues[$fundAllocation->sub_duration_id] ?? '-',
                    'total_available_amount' => $periodAvailableAmount,
                ]
            ),
            'componentMappings' => $componentMappings,
            'allocationHistory' => $allocationHistory,
            'documentUrl' => $this->getDocumentUrl($fundAllocation->document_path),
        ];
    }

    /**
     * Fetch Fund Allocation with relationships.
     */
    private function getFundAllocation(string $id): FundAllocation
    {
        return FundAllocation::with('componentMappings')->findOrFail($id);
    }

    /**
     * Format mappings for Edit screen.
     */
    private function formatComponentMappings(FundAllocation $fundAllocation)
    {
        return $fundAllocation->componentMappings->map(function ($mapping) {
            return [
                'id' => $mapping->id,
                'major_component_id' => $mapping->major_component_id,
                'sub_component_id' => $mapping->sub_component_id,
                'amount' => $mapping->amount,
            ];
        })->values();
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

    /**
     * Get document details for viewing or downloading.
     */
    public function getDocumentDetails(string $id): ?array
    {
        try {
            $fundAllocation = $this->getFundAllocation($id);
            if (!$fundAllocation || empty($fundAllocation->document_path)) {
                return null;
            }

            $file = DB::table('file_uploads')
                ->where('id', $fundAllocation->document_path)
                ->orWhere('file_system_name', $fundAllocation->document_path)
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
     * Get period summary (Opening Balance, Existing Fresh Allocations, Total Available Funds).
     */
    public function getPeriodSummary(string $financialYear, string $durationId, ?string $subDurationId = null, ?string $allocationId = null): array
    {
        if (empty($financialYear) || empty($durationId)) {
            return [
                'opening_balance' => 0.00,
                'existing_fresh_allocations' => 0.00,
                'total_available_funds' => 0.00,
                'has_existing_allocation' => false,
                'existing_allocated_amount' => 0.00,
                'has_enclosing_available_balance' => false,
                'enclosing_available_amount' => 0.00,
                'enclosing_duration_name' => null,
                'enclosing_sub_duration_name' => null,
            ];
        }

        // Dynamic schema update if the column doesn't exist
        if (!\Illuminate\Support\Facades\Schema::hasColumn('fund_allocations', 'total_available_amount')) {
            \Illuminate\Support\Facades\Schema::table('fund_allocations', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->decimal('total_available_amount', 15, 2)->default(0)->nullable()->after('total_amount_allocated');
            });
            // Migrate existing data based on the old calculation logic
            DB::statement("
                UPDATE fund_allocations fa
                LEFT JOIN (
                    SELECT allocation_id, COALESCE(SUM(amount), 0) as allocated
                    FROM fund_allocations_map
                    GROUP BY allocation_id
                ) fam ON fam.allocation_id = fa.id
                SET fa.total_available_amount = GREATEST(0, COALESCE(fa.total_amount_allocated, 0) - COALESCE(fam.allocated, 0))
            ");
        }

        // Reopening THIS SAME period: only money never assigned to any component
        // ("Unallocated") is available for a fresh/additional component allocation here.
        // Money already assigned to a component but not yet distributed ("Undistributed")
        // stays earmarked for that component within this period -- it only becomes
        // generally available again once the period closes and its remainder is carried
        // forward into a NEW period (see getCarryForwardEligibleAmount(), used by
        // checkPreviousRemainingBalance() for that separate popup).
        $openingBalance = $this->getUnallocatedRemainderForPeriod($financialYear, $durationId, $subDurationId);

        // Fresh allocations starts at 0 for a reopened period, as the remaining amount is in Opening Balance
        $existingFreshAllocations = 0.00;

        $existingAllocation = $this->getExistingAllocationForPeriod($financialYear, $durationId, $subDurationId, $allocationId);

        // Only worth checking whether a COARSER, chronologically-enclosing period (e.g.
        // the Half-Year a Quarter falls within) already has money available when this
        // exact period has no allocation note of its own -- if it already does, that note
        // (and the already-hidden Add button) covers it, and stacking a second note on
        // top would be confusing/duplicate.
        $hasEnclosingAvailable = false;
        $enclosingAvailableAmount = 0.00;
        $enclosingDurationName = null;
        $enclosingSubDurationName = null;

        if (!$existingAllocation['has_existing_allocation'] && !empty($subDurationId)) {
            $enclosing = $this->findEnclosingPeriodWithAvailableBalance($financialYear, $durationId, $subDurationId);
            if ($enclosing) {
                $hasEnclosingAvailable = true;
                $enclosingAvailableAmount = $enclosing['available_amount'];
                $enclosingDurationName = $enclosing['duration_name'];
                $enclosingSubDurationName = $enclosing['sub_duration_name'];
            }
        }

        return [
            'opening_balance' => $openingBalance,
            'existing_fresh_allocations' => $existingFreshAllocations,
            'total_available_funds' => $openingBalance + $existingFreshAllocations,
            'has_existing_allocation' => $existingAllocation['has_existing_allocation'],
            'existing_allocated_amount' => $existingAllocation['existing_allocated_amount'],
            'has_enclosing_available_balance' => $hasEnclosingAvailable,
            'enclosing_available_amount' => $enclosingAvailableAmount,
            'enclosing_duration_name' => $enclosingDurationName,
            'enclosing_sub_duration_name' => $enclosingSubDurationName,
        ];
    }

    /**
     * Find a coarser, chronologically-enclosing period (e.g. the Half-Year a Quarter or
     * Month falls within) that already has Fund Allocation activity with some amount
     * still UNALLOCATED (never assigned to any component) right now. Used to tell the
     * user money is already available for use in the currently-selected finer period
     * instead of letting them create a duplicate/overlapping Fresh Allocation on top of
     * it.
     *
     * Mirrors findAncestorPeriodWithAllocation() (used by Fund Distribution's
     * parent-period warning) but at the Fund Allocation HEADER level -- not scoped to one
     * component. Deliberately uses ONLY getUnallocatedRemainderForPeriod() here, NOT the
     * "unallocated + undistributed" formula getCarryForwardEligibleAmount() uses --
     * money already assigned to a component in the enclosing period but not yet
     * distributed stays earmarked for that component there (same reasoning as the
     * Opening Balance calculation above), it is not free to be assigned to a NEW
     * component at this finer granularity.
     */
    public function findEnclosingPeriodWithAvailableBalance(
        string $financialYear,
        string $durationId,
        string $subDurationId
    ): ?array {
        $selectedRange = $this->getFiscalMonthRange($durationId, $subDurationId);
        if (!$selectedRange) {
            return null;
        }
        [$selectedStart, $selectedEnd] = $selectedRange;

        foreach ($this->getActivePeriodsWithRanges($financialYear) as $period) {
            if ($period['duration_id'] === $durationId && $period['sub_duration_id'] === $subDurationId) {
                continue;
            }

            $encloses = $period['start'] <= $selectedStart && $period['end'] >= $selectedEnd;
            $strictlyCoarser = $period['start'] < $selectedStart || $period['end'] > $selectedEnd;

            if (!$encloses || !$strictlyCoarser) {
                continue;
            }

            $available = $this->getUnallocatedRemainderForPeriod($financialYear, $period['duration_id'], $period['sub_duration_id']);

            if ($available > 0) {
                $durationName = DB::table('attribute_values')->where('id', $period['duration_id'])->value('attribute_value');

                return [
                    'duration_id' => $period['duration_id'],
                    'sub_duration_id' => $period['sub_duration_id'],
                    'duration_name' => $durationName ?? '-',
                    'sub_duration_name' => $period['sub_duration_name'],
                    'available_amount' => $available,
                ];
            }
        }

        return null;
    }

    /**
     * Whether a Fund Allocation already exists for the EXACT financial year + duration +
     * sub-duration selected (as opposed to an enclosing/earlier period -- see
     * findAncestorPeriodWithAllocation() for that), and the total already allocated for it.
     * Used to decide whether the "Add Fresh Allocation" button should be shown on the
     * create/edit form, or a Note with the existing amount instead. $excludeAllocationId
     * excludes the record currently being edited so editing it doesn't look like a
     * duplicate of itself.
     */
    public function getExistingAllocationForPeriod(
        string $financialYear,
        string $durationId,
        ?string $subDurationId,
        ?string $excludeAllocationId = null
    ): array {
        $query = DB::table('fund_allocations')
            ->where('financial_year', $financialYear)
            ->where('duration_id', $durationId)
            ->when(
                !empty($subDurationId),
                fn($q) => $q->where('sub_duration_id', $subDurationId),
                fn($q) => $q->whereNull('sub_duration_id')
            );

        if (!empty($excludeAllocationId)) {
            $query->where('id', '!=', $excludeAllocationId);
        }

        $rows = $query->get(['id', 'total_amount_allocated']);

        // For Half-Yearly, this exact period's existing amount is already fully
        // reflected in the Opening Balance card (see getUnallocatedRemainderForPeriod()),
        // so surfacing it again as a separate "already exists" note would just be a
        // redundant/confusing duplicate of what Opening Balance already shows. Quarterly
        // (Q1/Q2, etc.) and other durations keep showing the note as before -- only
        // Half-Yearly rolls its own period this way. Same duration-name-based gating
        // style as the "yearly" skip in checkPreviousRemainingBalance().
        $durationName = strtolower((string) (DB::table('attribute_values')->where('id', $durationId)->value('attribute_value') ?? ''));
        $isHalfYearly = in_array($durationName, ['half-yearly', 'half yearly', 'half-year', 'half year', 'half_yearly'], true);

        return [
            'has_existing_allocation' => !$isHalfYearly && $rows->isNotEmpty(),
            'existing_allocated_amount' => (float) $rows->sum('total_amount_allocated'),
        ];
    }

    /**
     * "Current Allocation" for a component in a given period: how much is still
     * available on/attributable to it right now, considering everything that has
     * happened to its allocated money so far --
     *
     *   directly allocated (fund_allocation_component_mappings)
     *   + received via Component Utilization Mapping (this component as target)
     *   - transferred away via Component Utilization Mapping (this component as source)
     *   - already distributed (fund_distributions)
     *
     * All four figures are queried by the exact financial_year/duration/sub_duration
     * (never fund_pools), so this is correct regardless of pooling mode -- see
     * getUndistributedAmountsByComponent() for the same reasoning applied period-wide.
     */
    public function getComponentBalance(
        string $financialYear,
        string $durationId,
        ?string $subDurationId,
        string $majorComponentId,
        ?string $subComponentId
    ): array {
        if (empty($financialYear) || empty($durationId) || empty($majorComponentId)) {
            return ['remaining_balance' => 0.00, 'is_existing' => false];
        }

        // 1. Directly allocated to this component for this exact period
        $allocationQuery = DB::table('fund_allocation_component_mappings as facm')
            ->join('fund_allocations as fa', 'fa.id', '=', 'facm.fund_allocation_id')
            ->where('fa.financial_year', $financialYear)
            ->where('fa.duration_id', $durationId)
            ->where('facm.major_component_id', $majorComponentId);

        if (!empty($subDurationId)) {
            $allocationQuery->where('fa.sub_duration_id', $subDurationId);
        } else {
            $allocationQuery->whereNull('fa.sub_duration_id');
        }

        if (!empty($subComponentId)) {
            $allocationQuery->where('facm.sub_component_id', $subComponentId);
        } else {
            $allocationQuery->whereNull('facm.sub_component_id');
        }

        $exists = $allocationQuery->exists();
        $totalAllocated = (float) $allocationQuery->sum('facm.amount');

        // 2. Already distributed against this component for this exact period
        $distributionQuery = DB::table('fund_distributions')
            ->where('financial_year', $financialYear)
            ->where('duration_id', $durationId)
            ->where('major_component_id', $majorComponentId)
            ->whereNull('deleted_at');

        if (!empty($subDurationId)) {
            $distributionQuery->where('sub_duration_id', $subDurationId);
        } else {
            $distributionQuery->whereNull('sub_duration_id');
        }

        if (!empty($subComponentId)) {
            $distributionQuery->where('sub_component_id', $subComponentId);
        } else {
            $distributionQuery->whereNull('sub_component_id');
        }

        $distributed = (float) $distributionQuery->sum('distribution_amount');

        // 3. Component Utilization Mapping headers for this exact period
        $mappingQuery = DB::table('component_utilization_mappings')
            ->where('financial_year', $financialYear)
            ->where('duration', $durationId);

        if (!empty($subDurationId)) {
            $mappingQuery->where('sub_duration', $subDurationId);
        } else {
            $mappingQuery->whereNull('sub_duration');
        }

        $mappingIds = $mappingQuery->pluck('id');

        // 4. Transferred AWAY via Component Utilization Mapping (this component as source)
        $transferredAwayQuery = DB::table('component_utilization_mapping_details')
            ->whereIn('mapping_id', $mappingIds)
            ->where('source_major_component', $majorComponentId);

        if (!empty($subComponentId)) {
            $transferredAwayQuery->where('source_sub_component', $subComponentId);
        } else {
            $transferredAwayQuery->whereNull('source_sub_component');
        }

        $transferredAway = (float) $transferredAwayQuery->sum('amount_to_be_allocated');

        // 5. Received via Component Utilization Mapping (this component as target)
        $utilizationQuery = DB::table('component_utilization_mapping_details')
            ->whereIn('mapping_id', $mappingIds)
            ->where('target_category', $majorComponentId);

        if (!empty($subComponentId)) {
            $utilizationQuery->where('target_sub_component', $subComponentId);
        } else {
            $utilizationQuery->whereNull('target_sub_component');
        }

        $alreadyAllocated = (float) $utilizationQuery->sum('amount_to_be_allocated');

        // 6. Already carried forward AWAY from this exact period into a later period
        // (see FundCarryForwardDetail ledger written by StoreFundAllocationAction) --
        // without this, a period keeps showing its full original allocation even after
        // some of it has been swept forward.
        $carriedForwardAway = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('fund_carry_forward_logs')) {
            $carriedForwardQuery = DB::table('fund_carry_forward_logs')
                ->where('financial_year', $financialYear)
                ->where('from_duration_id', $subDurationId ?: $durationId)
                ->where('major_component_id', $majorComponentId);

            if (!empty($subComponentId)) {
                $carriedForwardQuery->where('sub_component_id', $subComponentId);
            } else {
                $carriedForwardQuery->whereNull('sub_component_id');
            }

            $createSum = (float) (clone $carriedForwardQuery)->where('action', 'CREATE')->sum('carried_amount');
            $reverseSum = (float) (clone $carriedForwardQuery)->where('action', 'REVERSE')->sum('carried_amount');

            $carriedForwardAway = max(0.0, $createSum - $reverseSum);
        }

        $currentAllocation = max(0.0, $totalAllocated + $alreadyAllocated - $transferredAway - $distributed - $carriedForwardAway);

        // dd([
        //     'financial_year' => $financialYear,
        //     'duration_id' => DB::table('attribute_values')->where('id', $durationId)->value('attribute_value'),
        //     'sub_duration_id' => $subDurationId ? DB::table('attribute_values')->where('id', $subDurationId)->value('attribute_value') : null,
        //     'major_component_id' => DB::table('attribute_values')->where('id', $majorComponentId)->value('attribute_value'),
        //     'sub_component_id' => $subComponentId ? DB::table('attribute_values')->where('id', $subComponentId)->value('attribute_value') : null,
        //     'remaining_balance' => $currentAllocation,
        //     'total_allocated' => $totalAllocated,
        //     'already_allocated' => $currentAllocation,
        //     'distributed' => $distributed,
        //     'transferred_away' => $transferredAway,
        //     'received_via_utilization' => $alreadyAllocated,
        //     'carried_forward_away' => $carriedForwardAway,
        //     'is_existing' => $exists,
        // ]);

        return [
            'remaining_balance' => $currentAllocation,
            'total_allocated' => $totalAllocated,
            'already_allocated' => $currentAllocation,
            'distributed' => $distributed,
            'transferred_away' => $transferredAway,
            'received_via_utilization' => $alreadyAllocated,
            'carried_forward_away' => $carriedForwardAway,
            'is_existing' => $exists,
        ];
    }

    /**
     * Sum of a component's allocated amount that has since been transferred away to
     * another component via Component Utilization Mapping for the exact same period.
     * Reuses the same source-side aggregate already used to compute "already allocated"
     * elsewhere (see ComponentUtilizationMappingController::getUtilizedComponents()).
     */
    public function getDistributedAwayAmount(
        string $financialYear,
        ?string $durationId,
        ?string $subDurationId,
        string $majorComponentId,
        ?string $subComponentId
    ): float {
        $mappingQuery = DB::table('component_utilization_mappings')
            ->where('financial_year', $financialYear)
            ->where('duration', $durationId);

        if (!empty($subDurationId)) {
            $mappingQuery->where('sub_duration', $subDurationId);
        } else {
            $mappingQuery->whereNull('sub_duration');
        }

        $mappingIds = $mappingQuery->pluck('id');
        if ($mappingIds->isEmpty()) {
            return 0.0;
        }

        $utilizationQuery = DB::table('component_utilization_mapping_details')
            ->whereIn('mapping_id', $mappingIds)
            ->where('source_major_component', $majorComponentId);

        if (!empty($subComponentId)) {
            $utilizationQuery->where('source_sub_component', $subComponentId);
        } else {
            $utilizationQuery->whereNull('source_sub_component');
        }

        return (float) $utilizationQuery->sum('amount_to_be_allocated');
    }

    public function getDynamicAvailableBalance(string $financialYear, ?string $durationId = null, ?string $subDurationId = null): float
    {
        $allocQuery = DB::table('fund_allocations')
            ->where('financial_year', $financialYear);

        if ($durationId) {
            $allocQuery->where('duration_id', $durationId);
        }
        if ($subDurationId) {
            $allocQuery->where('sub_duration_id', $subDurationId);
        } elseif ($durationId) {
            $allocQuery->whereNull('sub_duration_id');
        }

        $allocations = $allocQuery->get();
        $totalUnallocated = 0.0;

        foreach ($allocations as $alloc) {
            $mapped = (float) DB::table('fund_allocation_component_mappings')
                ->where('fund_allocation_id', $alloc->id)
                ->sum('amount');
            $unallocated = (float) $alloc->total_amount_allocated - $mapped;
            $totalUnallocated += $unallocated;
        }

        $poolQuery = DB::table('fund_pools')
            ->where('financial_year', $financialYear);

        if ($durationId) {
            $poolQuery->where('duration_id', $durationId);
        }
        if ($subDurationId) {
            $poolQuery->where('sub_duration_id', $subDurationId);
        } elseif ($durationId) {
            $poolQuery->whereNull('sub_duration_id');
        }

        $totalPoolRemaining = (float) $poolQuery->sum('remaining_balance');

        $mappingQuery = DB::table('component_utilization_mappings')
            ->where('financial_year', $financialYear);

        if ($durationId) {
            $mappingQuery->where('duration', $durationId);
        }
        if ($subDurationId) {
            $mappingQuery->where('sub_duration', $subDurationId);
        } elseif ($durationId) {
            $mappingQuery->whereNull('sub_duration');
        }

        $mappingIds = $mappingQuery->pluck('id');
        $totalUtilizationMapped = 0.0;
        if ($mappingIds->isNotEmpty()) {
            $totalUtilizationMapped = (float) DB::table('component_utilization_mapping_details')
                ->whereIn('mapping_id', $mappingIds)
                ->sum('amount_to_be_allocated');
        }

        return $totalUnallocated + $totalPoolRemaining - $totalUtilizationMapped;
    }

    public function checkPreviousRemainingBalance(string $financialYear, string $durationId, ?string $subDurationId = null): array
    {
        if (empty($financialYear) || empty($durationId)) {
            return [
                'has_previous_balance' => false,
                'amount' => 0.00,
                'source_sub_duration_name' => '',
            ];
        }

        $duration = DB::table('attribute_values')->where('id', $durationId)->first();
        $durationName = strtolower($duration->attribute_value ?? '');

        // Do not show the carry-forward note for Yearly duration (it uses a separate yearly note)
        // or if the sub-duration hasn't been selected yet.
        if ($durationName === 'yearly' || empty($subDurationId)) {
            return [
                'has_previous_balance' => false,
                'amount' => 0.00,
                'source_sub_duration_name' => '',
                'duration_type' => $durationName,
            ];
        }

        // The popup only ever offers chronological carry-forward when crossing from an earlier HALF
        // of the financial year into this one. However, cross-duration (child->parent) carry forwards
        // (e.g. Monthly January -> Quarterly Jan-Mar) are exempt from this half-year restriction.
        $currentHalf = $this->resolveHalfYearLabel($durationId, $subDurationId);

        $durationName = DB::table('attribute_values')->where('id', $durationId)->value('attribute_value');

        $sourcePeriods = $this->resolveCarryForwardSourcePeriods($financialYear, $durationId, $subDurationId)
            ->filter(function ($period) use ($durationId, $currentHalf, $durationName) {
                // If it's a child->parent carry forward (different duration), allow it.
                if ($period['duration_id'] !== $durationId) {
                    return true;
                }

                // If it's chronological (same duration), enforce the half-year crossing rule.
                if ($currentHalf !== 'Second Half' && $durationName === 'half-yearly') {
                    return false;
                }

                if ($durationName === 'half-yearly') {
                    return $this->resolveHalfYearLabel($period['duration_id'], $period['sub_duration_id']) === 'First Half';
                }

                return true;
            })
            ->values();


        if ($sourcePeriods->isEmpty()) {
            return [
                'has_previous_balance' => false,
                'amount' => 0.00,
                'source_sub_duration_name' => '',
                'duration_type' => $durationName,
            ];
        }

        $amount = 0.0;
        foreach ($sourcePeriods as $period) {
            $amount += $this->getUndistributedRemainderForPeriod($financialYear, $period['duration_id'], $period['sub_duration_id']);
            $amount += $this->getUnallocatedRemainderForPeriod($financialYear, $period['duration_id'], $period['sub_duration_id']);
        }

        if ($amount > 0) {
            return [
                'has_previous_balance' => true,
                'amount' => $amount,
                'source_sub_duration_name' => $sourcePeriods->first()['sub_duration_name'],
                'duration_type' => $durationName,
            ];
        }

        return [
            'has_previous_balance' => false,
            'amount' => 0.00,
            'source_sub_duration_name' => '',
            'duration_type' => $durationName,
        ];
    }

    public function getYearlyNoteBalance(string $financialYear)
    {
        // Use the dynamically calculated carry-forward balance for the entire financial year
        $dynamicAmount = $this->getDynamicAvailableBalance($financialYear);

        return [
            'total_balance' => $dynamicAmount
        ];
    }

    /**
     * Resolve every (duration_id, sub_duration_id) pair that has ANY allocation activity
     * in this financial year, together with the fiscal-month range [start, end] (1-12,
     * April = 1) it covers. Used to compare periods of DIFFERENT granularities (Monthly
     * vs Quarterly vs Half-Yearly) on a common timeline.
     *
     * @return \Illuminate\Support\Collection<int, array{duration_id: string, sub_duration_id: string, sub_duration_name: string, start: int, end: int}>
     */
    private function getActivePeriodsWithRanges(string $financialYear): \Illuminate\Support\Collection
    {
        $combos = collect();

        foreach (DB::table('fund_allocations')->where('financial_year', $financialYear)->select('duration_id', 'sub_duration_id')->distinct()->get() as $c) {
            $combos->push([$c->duration_id, $c->sub_duration_id]);
        }
        foreach (DB::table('fund_pools')->where('financial_year', $financialYear)->select('duration_id', 'sub_duration_id')->distinct()->get() as $c) {
            $combos->push([$c->duration_id, $c->sub_duration_id]);
        }

        $combos = $combos->filter(fn($c) => !empty($c[0]) && !empty($c[1]))->unique(fn($c) => $c[0] . '|' . $c[1]);

        $result = collect();
        foreach ($combos as [$candDurationId, $candSubDurationId]) {
            $range = $this->getFiscalMonthRange($candDurationId, $candSubDurationId);
            if (!$range) {
                continue;
            }

            $subDuration = DB::table('attribute_values')->where('id', $candSubDurationId)->first();
            $result->push([
                'duration_id' => $candDurationId,
                'duration_name' => DB::table('attribute_values')->where('id', $candDurationId)->value('attribute_value') ?? '-',
                'sub_duration_id' => $candSubDurationId,
                'sub_duration_name' => $subDuration->attribute_value ?? '-',
                'start' => $range[0],
                'end' => $range[1],
            ]);
        }

        return $result;
    }

    /**
     * Map a (duration_id, sub_duration_id) pair to the [start, end] fiscal-month range
     * (1-12, April = 1) it covers, so periods of different granularities can be compared
     * chronologically. Returns null for Yearly-without-sub-duration or anything
     * unresolvable (treated as "whole year", excluded from cross-period comparisons since
     * it always overlaps everything). Public so other Fund Flow services (e.g. Fund
     * Distribution's parent-period check) can compare periods on the same timeline
     * instead of re-deriving this same fiscal-calendar math.
     */
    public function getFiscalMonthRange(string $durationId, ?string $subDurationId): ?array
    {
        if (empty($subDurationId)) {
            return null;
        }

        $duration = DB::table('attribute_values')->where('id', $durationId)->first();
        $subDuration = DB::table('attribute_values')->where('id', $subDurationId)->first();
        if (!$duration || !$subDuration) {
            return null;
        }

        $durationName = strtolower($duration->attribute_value ?? '');
        $subValue = strtolower($subDuration->attribute_value ?? '');
        $subCode = strtolower($subDuration->code ?? '');

        if (in_array($durationName, ['monthly', 'month'], true)) {
            $monthMap = [
                'april' => 1,
                'may' => 2,
                'june' => 3,
                'july' => 4,
                'august' => 5,
                'september' => 6,
                'october' => 7,
                'november' => 8,
                'december' => 9,
                'january' => 10,
                'february' => 11,
                'march' => 12,
            ];
            foreach ($monthMap as $monthNameKey => $pos) {
                if (str_contains($subValue, $monthNameKey) || str_contains($subCode, $monthNameKey)) {
                    return [$pos, $pos];
                }
            }

            $aprilSortOrder = DB::table('attribute_values')
                ->where('parent_id', $durationId)
                ->where('attribute_value', 'like', '%April%')
                ->value('sort_order');

            if ($aprilSortOrder !== null) {
                $position = (($subDuration->sort_order - $aprilSortOrder + 12) % 12) + 1;
                return [$position, $position];
            }
        }

        if (in_array($durationName, ['quarterly', 'quarter'], true)) {
            if (str_contains($subValue, 'first quarter') || str_contains($subValue, '1st quarter') || str_contains($subCode, 'q1') || str_contains($subValue, 'q1') || str_contains($subValue, 'first_quarter')) {
                return [1, 3];
            }
            if (str_contains($subValue, 'second quarter') || str_contains($subValue, '2nd quarter') || str_contains($subCode, 'q2') || str_contains($subValue, 'q2') || str_contains($subValue, 'second_quarter')) {
                return [4, 6];
            }
            if (str_contains($subValue, 'third quarter') || str_contains($subValue, '3rd quarter') || str_contains($subCode, 'q3') || str_contains($subValue, 'q3') || str_contains($subValue, 'third_quarter')) {
                return [7, 9];
            }
            if (str_contains($subValue, 'fourth quarter') || str_contains($subValue, '4th quarter') || str_contains($subCode, 'q4') || str_contains($subValue, 'q4') || str_contains($subValue, 'fourth_quarter')) {
                return [10, 12];
            }
        }

        if (in_array($durationName, ['half-yearly', 'half yearly', 'half-year', 'half year', 'half_yearly'], true)) {
            if (str_contains($subValue, 'first half') || str_contains($subValue, '1st half') || str_contains($subCode, 'h1') || str_contains($subValue, 'h1') || str_contains($subValue, 'first_half')) {
                return [1, 6];
            }
            if (str_contains($subValue, 'second half') || str_contains($subValue, '2nd half') || str_contains($subCode, 'h2') || str_contains($subValue, 'h2') || str_contains($subValue, 'second_half')) {
                return [7, 12];
            }
        }

        $monthsPerUnit = match (true) {
            in_array($durationName, ['quarterly', 'quarter'], true) => 3,
            in_array($durationName, ['half-yearly', 'half yearly', 'half-year', 'half year', 'half_yearly'], true) => 6,
            default => null,
        };

        if ($monthsPerUnit === null) {
            return null;
        }

        // sub_duration sort_order values are NOT small sequential integers comparable
        // across duration types (e.g. Quarterly sub-durations are seeded as 60/80/90/100,
        // Half-Yearly as 20/30) -- what IS reliable is ordinal RANK among a duration's own
        // siblings (Q1 = rank 1, Q2 = rank 2, ...).
        $rank = 1 + DB::table('attribute_values')
            ->where('parent_id', $durationId)
            ->where('sort_order', '<', $subDuration->sort_order)
            ->count();

        $end = $rank * $monthsPerUnit;
        $start = $end - $monthsPerUnit + 1;

        return [$start, $end];
    }

    /**
     * Convert a (duration_id, sub_duration_id) period into its actual calendar date range
     * within the given financial year, using the fiscal mapping April = fiscal month 1
     * (Q1 = April-June, Q2 = July-September, Q3 = October-December, Q4 = January-March;
     * First Half = Q1+Q2 = April-September, Second Half = Q3+Q4 = October-March). Returns
     * null for Yearly-without-sub-duration or anything unresolvable, since that case is
     * already covered by the whole-financial-year check callers already perform.
     *
     * @return array{0: \Carbon\Carbon, 1: \Carbon\Carbon}|null [start, end]
     */
    public function getFiscalPeriodDateRange(string $financialYear, string $durationId, ?string $subDurationId): ?array
    {
        $range = $this->getFiscalMonthRange($durationId, $subDurationId);
        if (!$range) {
            return null;
        }
        [$fiscalStart, $fiscalEnd] = $range;

        $parts = explode('-', $financialYear);
        if (count($parts) !== 2) {
            return null;
        }
        $startYear = (int) $parts[0];
        $endYear = (int) $parts[1];

        // Fiscal position 1 (April) .. 9 (December) fall in the FY's start year;
        // 10 (January) .. 12 (March) fall in the FY's end year.
        $toCalendarMonth = fn(int $p) => (($p + 2) % 12) + 1;
        $toCalendarYear = fn(int $p) => $p <= 9 ? $startYear : $endYear;

        $minDate = \Carbon\Carbon::create($toCalendarYear($fiscalStart), $toCalendarMonth($fiscalStart), 1)->startOfDay();
        $maxDate = \Carbon\Carbon::create($toCalendarYear($fiscalEnd), $toCalendarMonth($fiscalEnd), 1)->endOfMonth()->endOfDay();

        return [$minDate, $maxDate];
    }

    /**
     * Which half of the financial year (First Half = Q1+Q2 = April-September, Second Half
     * = Q3+Q4 = October-March) a Quarterly or Monthly period falls entirely within. Returns
     * null for anything that spans both halves (e.g. Yearly) or isn't a Quarterly/Monthly
     * period, since "half" isn't a meaningful label for those.
     */
    public function resolveHalfYearLabel(string $durationId, ?string $subDurationId): ?string
    {
        $range = $this->getFiscalMonthRange($durationId, $subDurationId);
        if (!$range) {
            return null;
        }
        [$start, $end] = $range;

        if ($end <= 6) {
            return 'First Half';
        }
        if ($start >= 7) {
            return 'Second Half';
        }

        return null;
    }

    /**
     * Resolve every period eligible to carry forward INTO the given destination period,
     * following the financial-year calendar timeline (April - March) rather than
     * comparing raw sort_order/duration-type: any other period with allocation activity
     * this financial year whose fiscal-month range ends at or before the destination's
     * own range ends (i.e. entirely before it, or one of its own finer-grained
     * constituent periods -- e.g. Q1/Q2 and April-September for a First Half
     * destination) is eligible. This lets a chain like First Half -> Third Quarter ->
     * Second Half correctly carry forward at each step, even though Half-Yearly,
     * Quarterly and Monthly are unrelated attribute_values hierarchies. Shared by the
     * carry-forward popup check and the actual auto-carry-forward sweep so both always
     * agree on the same set of source periods.
     *
     * @return \Illuminate\Support\Collection<int, array{duration_id: string, sub_duration_id: string, sub_duration_name: string}>
     */
    public function resolveCarryForwardSourcePeriods(string $financialYear, string $durationId, string $subDurationId): \Illuminate\Support\Collection
    {
        $destinationRange = $this->getFiscalMonthRange($durationId, $subDurationId);

        if (!$destinationRange) {
            return collect();
        }
        [, $destinationEnd] = $destinationRange;



        return $this->getActivePeriodsWithRanges($financialYear)
            ->filter(function ($period) use ($durationId, $subDurationId, $destinationEnd, $destinationRange) {
                if ($period['duration_id'] === $durationId && $period['sub_duration_id'] === $subDurationId) {
                    return false;
                }

                // If it's the SAME duration type, standard chronological carry-forward applies.
                // If it's a DIFFERENT duration type, we ONLY allow it if the destination
                // period strictly ENCLOSES the source period (e.g. Quarterly Jan-Mar enclosing Monthly January),
                // as requested by the user to prevent unrelated cross-duration pollution.
                if ($period['duration_id'] === $durationId) {
                    return $period['end'] <= $destinationEnd;
                }

                [$destStart, $destEnd] = $destinationRange;

                $durationName = DB::table('attribute_values')->where('id', $durationId)->value('attribute_value');

                if ($durationName === 'Quarterly' && $period['duration_name'] === 'Half Yearly') {
                    return $period['start'] >= 1 && $period['end'] <= 6 && $destStart >= 7 && $destEnd <= 12;
                }

                if ($durationName === 'Monthly' && $period['duration_name'] === 'Half Yearly') {
                    return $period['start'] >= 1 && $period['end'] <= 6 && in_array($destStart, [7, 8, 9, 10, 11, 12]) && in_array($destEnd, [7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Monthly' && $period['duration_name'] === 'Quarterly' && $period['sub_duration_name'] === 'First Quarter') {
                    return $period['start'] >= 1 && $period['end'] <= 3 && in_array($destStart, [4, 5, 6, 7, 8, 9, 10, 11, 12]) && in_array($destEnd, [4, 5, 6, 7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Monthly' && $period['duration_name'] === 'Quarterly' && $period['sub_duration_name'] === 'Second Quarter') {
                    return $period['start'] >= 4 && $period['end'] <= 6 && in_array($destStart, [7, 8, 9, 10, 11, 12]) && in_array($destEnd, [7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Monthly' && $period['duration_name'] === 'Quarterly' && $period['sub_duration_name'] === 'Third Quarter') {
                    return $period['start'] >= 7 && $period['end'] <= 9 && in_array($destStart, [10, 11, 12]) && in_array($destEnd, [10, 11, 12]);
                }

                if ($durationName === 'Monthly' && $period['duration_name'] === 'Quarterly' && $period['sub_duration_name'] === 'Fourth Quarter') {
                    return $period['start'] >= 10 && $period['end'] <= 12 && in_array($destStart, []) && in_array($destEnd, []);
                }

                if ($durationName === 'Quarterly' && $period['duration_name'] === 'Monthly' && in_array($period['sub_duration_name'], ['April', 'May', 'June'])) {
                    return $period['start'] >= 1 && $period['end'] <= 1 && in_array($destStart, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]) && in_array($destEnd, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Quarterly' && $period['duration_name'] === 'Monthly' && in_array($period['sub_duration_name'], ['July', 'August', 'September'])) {
                    return $period['start'] >= 1 && $period['end'] <= 1 && in_array($destStart, [4, 5, 6, 7, 8, 9, 10, 11, 12]) && in_array($destEnd, [4, 5, 6, 7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Quarterly' && $period['duration_name'] === 'Monthly' && in_array($period['sub_duration_name'], ['October', 'November', 'December'])) {
                    return $period['start'] >= 1 && $period['end'] <= 1 && in_array($destStart, [7, 8, 9, 10, 11, 12]) && in_array($destEnd, [7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Quarterly' && $period['duration_name'] === 'Monthly' && in_array($period['sub_duration_name'], ['January', 'February', 'March'])) {
                    return $period['start'] >= 1 && $period['end'] <= 1 && in_array($destStart, [10, 11, 12]) && in_array($destEnd, [10, 11, 12]);
                }


                if ($durationName === 'Half Yearly' && $period['duration_name'] === 'Monthly' && in_array($period['sub_duration_name'], ['October', 'November', 'December', 'January', 'February', 'March'])) {
                    return $period['start'] >= 1 && $period['end'] <= 6 && in_array($destStart, [7, 8, 9, 10, 11, 12]) && in_array($destEnd, [7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Half Yearly' && $period['duration_name'] === 'Quarterly' && in_array($period['sub_duration_name'], ['First Quarter', 'Second Quarter'])) {
                    return $period['start'] >= 1 && $period['end'] <= 6 && in_array($destStart, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]) && in_array($destEnd, [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]);
                }

                if ($durationName === 'Half Yearly' && $period['duration_name'] === 'Quarterly' && in_array($period['sub_duration_name'], ['Third Quarter', 'Fourth Quarter'])) {
                    return $period['start'] >= 1 && $period['end'] <= 6 && in_array($destStart, [7, 8, 9, 10, 11, 12]) && in_array($destEnd, [7, 8, 9, 10, 11, 12]);
                }



                return $period['start'] >= $destStart && $period['end'] <= $destEnd;
            })
            ->map(fn($period) => [
                'duration_id' => $period['duration_id'],
                'sub_duration_id' => $period['sub_duration_id'],
                'sub_duration_name' => $period['sub_duration_name'],
            ])
            ->values();
    }

    /**
     * Find a coarser, chronologically-enclosing period (e.g. the Half-Year a Month or
     * Quarter falls within) that already has a Fund Allocation for the given component,
     * using the same fiscal-month-range comparison as resolveCarryForwardSourcePeriods()
     * rather than duration-type-specific parent/child assumptions. Used to warn "an
     * allocation already exists for the enclosing period" instead of a plain "not found"
     * when the exact selected period has none of its own.
     */
    public function findAncestorPeriodWithAllocation(
        string $financialYear,
        string $durationId,
        string $subDurationId,
        string $majorComponentId,
        ?string $subComponentId
    ): ?array {
        $selectedRange = $this->getFiscalMonthRange($durationId, $subDurationId);
        if (!$selectedRange) {
            return null;
        }
        [$selectedStart, $selectedEnd] = $selectedRange;

        $candidates = DB::table('fund_allocation_component_mappings as facm')
            ->join('fund_allocations as fa', 'fa.id', '=', 'facm.fund_allocation_id')
            ->where('fa.financial_year', $financialYear)
            ->where('facm.major_component_id', $majorComponentId)
            ->when(
                !empty($subComponentId),
                fn($q) => $q->where('facm.sub_component_id', $subComponentId),
                fn($q) => $q->whereNull('facm.sub_component_id')
            )
            ->select('fa.duration_id', 'fa.sub_duration_id')
            ->distinct()
            ->get();

        foreach ($candidates as $candidate) {
            if ($candidate->duration_id === $durationId && $candidate->sub_duration_id === $subDurationId) {
                continue;
            }

            $candidateRange = $this->getFiscalMonthRange($candidate->duration_id, $candidate->sub_duration_id);
            if (!$candidateRange) {
                continue;
            }
            [$candidateStart, $candidateEnd] = $candidateRange;

            $encloses = $candidateStart <= $selectedStart && $candidateEnd >= $selectedEnd;
            $strictlyCoarser = $candidateStart < $selectedStart || $candidateEnd > $selectedEnd;

            if ($encloses && $strictlyCoarser) {
                $subDuration = DB::table('attribute_values')->where('id', $candidate->sub_duration_id)->first();

                return [
                    'duration_id' => $candidate->duration_id,
                    'sub_duration_id' => $candidate->sub_duration_id,
                    'sub_duration_name' => $subDuration->attribute_value ?? '-',
                ];
            }
        }

        return null;
    }

    /**
     * Sum of a source period's still-unallocated header remainder
     * (total_amount_allocated - Σ component mappings) across all Fund Allocations made
     * in that exact duration/sub-duration, minus whatever portion of it has already been
     * swept into a later period by a previous carry-forward (tracked via
     * fund_carry_forward_details using the UNALLOCATED_COMPONENT sentinel, since this
     * remainder isn't tied to any one component). Without that ledger subtraction this
     * amount would be offered/carried again on every subsequent carry-forward from the
     * same source period.
     */
    public function getUnallocatedRemainderForPeriod(string $financialYear, string $durationId, ?string $subDurationId): float
    {
        $allocations = DB::table('fund_allocations')
            ->where('financial_year', $financialYear)
            ->where('duration_id', $durationId)
            ->when(
                !empty($subDurationId),
                fn($q) => $q->where('sub_duration_id', $subDurationId),
                fn($q) => $q->whereNull('sub_duration_id')
            )
            ->get(['id', 'total_amount_allocated']);

        $totalUnallocated = 0.0;
        foreach ($allocations as $alloc) {
            $mapped = (float) DB::table('fund_allocation_component_mappings')
                ->where('fund_allocation_id', $alloc->id)
                ->sum('amount');
            $totalUnallocated += (float) $alloc->total_amount_allocated - $mapped;
        }

        $alreadyCarried = 0.0;
        if (\Illuminate\Support\Facades\Schema::hasTable('fund_carry_forward_logs')) {
            $alreadyCarriedQuery = DB::table('fund_carry_forward_logs')
                ->where('financial_year', $financialYear)
                ->where('from_duration_id', $subDurationId ?: $durationId)
                ->where('major_component_id', \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT);

            $createSum = (float) (clone $alreadyCarriedQuery)->where('action', 'CREATE')->sum('carried_amount');
            $reverseSum = (float) (clone $alreadyCarriedQuery)->where('action', 'REVERSE')->sum('carried_amount');

            $alreadyCarried = max(0.0, $createSum - $reverseSum);
        }

        return max(0.0, $totalUnallocated - $alreadyCarried);
    }

    /**
     * Per-component breakdown of a period's still-undistributed money: for every
     * component allocated to in that exact period, allocated amount minus whatever has
     * since been distributed against it, minus whatever portion has already been swept
     * into a later period by a previous carry-forward (ledger-tracked, keyed by the
     * component's own id this time, unlike the unallocated-remainder ledger).
     *
     * Deliberately computed from fund_allocation_component_mappings/fund_distributions
     * directly (both of which retain duration_id/sub_duration_id per row) rather than
     * from fund_pools -- this app can run in "merged" pooling mode
     * (config('allocation.pooling_mode') === 2), where a single fund_pools row is shared
     * across an entire component's whole financial year regardless of period, so it
     * cannot answer "how much of THIS period's allocation is still undistributed." This
     * calculation gives the same correct answer in either pooling mode.
     *
     * @return array<string, float> keyed by "{major_component_id}_{sub_component_id|NULL}"
     */
    public function getUndistributedAmountsByComponent(string $financialYear, string $durationId, ?string $subDurationId): array
    {
        $components = [];

        // 1. Get components with direct mappings
        $mappings = DB::table('fund_allocation_component_mappings as facm')
            ->join('fund_allocations as fa', 'fa.id', '=', 'facm.fund_allocation_id')
            ->where('fa.financial_year', $financialYear)
            ->where('fa.duration_id', $durationId)
            ->when(
                !empty($subDurationId),
                fn($q) => $q->where('fa.sub_duration_id', $subDurationId),
                fn($q) => $q->whereNull('fa.sub_duration_id')
            )
            ->select('facm.major_component_id', 'facm.sub_component_id')
            ->distinct()
            ->get();

        foreach ($mappings as $m) {
            $key = $m->major_component_id . '_' . ($m->sub_component_id ?? 'NULL');
            $components[$key] = ['major' => $m->major_component_id, 'sub' => $m->sub_component_id];
        }

        // 2. Get components that received funds via Component Utilization Mapping
        $mappingQuery = DB::table('component_utilization_mappings')
            ->where('financial_year', $financialYear)
            ->where('duration', $durationId);

        if (!empty($subDurationId)) {
            $mappingQuery->where('sub_duration', $subDurationId);
        } else {
            $mappingQuery->whereNull('sub_duration');
        }

        $mappingIds = $mappingQuery->pluck('id');
        if ($mappingIds->isNotEmpty()) {
            $received = DB::table('component_utilization_mapping_details')
                ->whereIn('mapping_id', $mappingIds)
                ->select('target_category as major_component_id', 'target_sub_component as sub_component_id')
                ->distinct()
                ->get();

            foreach ($received as $r) {
                $key = $r->major_component_id . '_' . ($r->sub_component_id ?? 'NULL');
                $components[$key] = ['major' => $r->major_component_id, 'sub' => $r->sub_component_id];
            }
        }

        // 3. Compute true remaining balance for each component
        $undistributed = [];
        foreach ($components as $key => $comp) {
            $balance = $this->getComponentBalance(
                $financialYear,
                $durationId,
                $subDurationId,
                $comp['major'],
                $comp['sub']
            );

            if ($balance['remaining_balance'] > 0) {
                $undistributed[$key] = $balance['remaining_balance'];
            }
        }

        return $undistributed;
    }

    /**
     * Total still-undistributed money across all components in a period. See
     * getUndistributedAmountsByComponent() for the per-component breakdown and why this
     * is computed from allocation/distribution records rather than fund_pools.
     */
    public function getUndistributedRemainderForPeriod(string $financialYear, string $durationId, ?string $subDurationId): float
    {
        return array_sum($this->getUndistributedAmountsByComponent($financialYear, $durationId, $subDurationId));
    }

    /**
     * Total amount eligible to carry forward into the destination period: for every
     * source period resolved by resolveCarryForwardSourcePeriods(), its still-undistributed
     * money (allocated to a component but not yet paid out) plus its still-unallocated
     * header remainder (never assigned to any component). This is the single formula
     * used both to advertise the carry-forward amount (the popup) and to actually move
     * it (StoreFundAllocationAction), so the two can never disagree.
     */
    public function getCarryForwardEligibleAmount(string $financialYear, string $durationId, string $subDurationId): float
    {
        $total = 0.0;

        foreach ($this->resolveCarryForwardSourcePeriods($financialYear, $durationId, $subDurationId) as $period) {
            $total += $this->getUndistributedRemainderForPeriod($financialYear, $period['duration_id'], $period['sub_duration_id']);
            $total += $this->getUnallocatedRemainderForPeriod($financialYear, $period['duration_id'], $period['sub_duration_id']);
        }

        return $total;
    }

    public function processCarryForwardForPreviousAmount(string $financialYear, string $durationId, string $subDurationId) {}
}
