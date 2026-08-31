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
        7 => 'total_allocated_amount',
        8 => 'fa.total_available_amount',
        9 => 'fa.created_at',
    ];

    /**
     * Execute the list fund allocation action.
     *
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        // "Remaining"/"Total Available" reflects what hasn't yet been assigned to any
        // component out of the WHOLE PERIOD's sanctioned total (financial_year +
        // duration + sub_duration), not just this one header row. Multiple Fund
        // Allocation records can legitimately share the same exact period (e.g. a
        // second allocation created later that spends down the period's existing
        // Opening Balance instead of adding a fresh amount of its own, matching the
        // Add-Allocation form's own period-wide Opening Balance) -- computing this
        // per-row instead (this row's own total_amount_allocated - this row's own
        // mapped amount) would go negative for any row whose own total_amount_allocated
        // is 0 while it still has component mappings, even though the period overall
        // still has a healthy positive balance. Pool remaining-balance and
        // utilization-mapping figures are NOT part of this: they describe what has
        // happened to money AFTER it was assigned to a component (distributed, or moved
        // elsewhere), which is a different concern from "how much of this period's
        // sanction is still unassigned" and would double-count if added here (see
        // FundAllocationService::getDynamicAvailableBalance()/getCarryForwardEligibleAmount()
        // for the figures that DO combine those signals, e.g. for Opening Balance and
        // carry-forward).
        $periodAllocatedSubquery = "(SELECT COALESCE(SUM(fa2.total_amount_allocated), 0)
            FROM fund_allocations fa2
            WHERE fa2.financial_year = fa.financial_year
            AND fa2.duration_id = fa.duration_id
            AND (fa2.sub_duration_id = fa.sub_duration_id OR (fa2.sub_duration_id IS NULL AND fa.sub_duration_id IS NULL)))";

        $periodMappedSubquery = "(SELECT COALESCE(SUM(facm2.amount), 0)
            FROM fund_allocation_component_mappings facm2
            INNER JOIN fund_allocations fa3 ON fa3.id = facm2.fund_allocation_id
            WHERE fa3.financial_year = fa.financial_year
            AND fa3.duration_id = fa.duration_id
            AND (fa3.sub_duration_id = fa.sub_duration_id OR (fa3.sub_duration_id IS NULL AND fa.sub_duration_id IS NULL)))";

        // Portion of this period's unmapped remainder that has ALREADY been swept forward
        // into a later period by the auto carry-forward sweep (StoreFundAllocationAction),
        // tracked via the UNALLOCATED_COMPONENT sentinel in fund_carry_forward_details --
        // same subtraction FundAllocationService::getUnallocatedRemainderForPeriod() applies
        // for the Create-page Opening Balance. Without it, this List page keeps showing a
        // period's original available amount forever, never reflecting that it was carried
        // away (see FundAllocationService::getUnallocatedRemainderForPeriod() docblock).
        $periodAlreadyCarriedSubquery = "(SELECT 0)";
        if (\Illuminate\Support\Facades\Schema::hasTable('fund_carry_forwards')) {
            $periodAlreadyCarriedSubquery = "(SELECT COALESCE(SUM(fcfd2.carried_forward_amount), 0)
                FROM fund_carry_forward_details fcfd2
                INNER JOIN fund_carry_forwards fcf2 ON fcf2.id = fcfd2.carry_forward_id
                WHERE fcf2.financial_year = fa.financial_year
                AND fcf2.from_duration_id = fa.duration_id
                AND (fcf2.from_sub_duration_id = fa.sub_duration_id OR (fcf2.from_sub_duration_id IS NULL AND fa.sub_duration_id IS NULL))
                AND fcfd2.major_component_id = '" . \App\Domain\FundCarryForward\FundCarryForwardDetail::UNALLOCATED_COMPONENT . "')";
        }

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
                DB::raw('COALESCE(fa.total_amount_allocated, 0) as total_fresh_amount'),
                DB::raw('COALESCE(SUM(facm.amount), 0) as total_allocated_amount'),
                // Floored at 0, matching every other "remaining balance" calculation in
                // this module (getUnallocatedRemainderForPeriod(), getComponentBalance(),
                // etc.) -- this is a period-wide figure computed independently of the
                // per-row stored snapshot, so it must never be allowed to render negative.
                DB::raw("GREATEST(0, $periodAllocatedSubquery - $periodMappedSubquery - $periodAlreadyCarriedSubquery) as remaining_amount"),
                DB::raw("GREATEST(0, $periodAllocatedSubquery - $periodMappedSubquery - $periodAlreadyCarriedSubquery) as total_available_amount")
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
                    ->orWhere('total_fresh_amount', 'like', "%$search%")
                    ->orWhere('total_allocated_amount', 'like', "%$search%")
                    ->orWhere('total_available_amount', 'like', "%$search%");
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
