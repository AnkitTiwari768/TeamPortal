<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UniqueFundFlow implements Rule
{
    protected $financialYear;
    protected $duration;
    protected $durationLimit;
    protected $ignoreAllocationId; // for edit allocation
    protected $ignoreMapId;        // for edit map row

    public function __construct($financialYear, $duration, $durationLimit = null, $ignoreAllocationId = null, $ignoreMapId = null)
    {
        $this->financialYear      = $financialYear;
        $this->duration           = $duration;
        $this->durationLimit      = $durationLimit;
        $this->ignoreAllocationId = $ignoreAllocationId;
        $this->ignoreMapId        = $ignoreMapId;
    }

    public function passes($attribute, $value)
    {
        $data = request()->all();

        // extract index from personalities_details.*.xxx
        preg_match('/personalities_details\.(\d+)\./', $attribute, $matches);
        $index = $matches[1] ?? null;

        if ($index === null) {
            return true;
        }

        $row = $data['personalities_details'][$index] ?? null;
        if (!$row) {
            return true;
        }

        // 👇 get ignoreMapId directly from row (only present on edit)
        $ignoreMapId = $row['id'] ?? null;

        // find allocations that match financial_year + duration + duration_limit
        $allocationQuery = DB::table('fund_allocations')
            ->where('financial_year', $this->financialYear)
            ->where('duration', $this->duration)
            ->when($this->durationLimit, fn($q) => $q->where('duration_limit', $this->durationLimit));

        if ($this->ignoreAllocationId) {
            $allocationQuery->where('id', '!=', $this->ignoreAllocationId);
        }

        $allocationIds = $allocationQuery->pluck('id');

        if ($allocationIds->isEmpty()) {
            return true;
        }

        $query = DB::table('fund_allocations_map')
            ->whereIn('allocation_id', $allocationIds)
            ->where('major_component_id', $row['major_component_id']);

        if (!empty($row['component_id'])) {
            $query->where('component_id', $row['component_id']);
        } else {
            $query->whereNull('component_id');
        }

        if (!empty($row['sub_component_id'])) {
            $query->where('sub_component_id', $row['sub_component_id']);
        } else {
            $query->whereNull('sub_component_id');
        }

        if ($ignoreMapId) {
            $query->where('id', '!=', $ignoreMapId);
        }

        return !$query->exists();
    }

    public function message()
    {
        return "Duplicate combination: This Major/Component/Sub-component already exists for the same Financial Year, Duration and Duration Limit.";
    }
}
