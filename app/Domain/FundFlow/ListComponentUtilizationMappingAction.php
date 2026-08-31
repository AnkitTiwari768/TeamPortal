<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListComponentUtilizationMappingAction
{
    use DataTable;

    protected array $columns = [
        1 => 'cum.financial_year',
        2 => 'dur.attribute_value',
        3 => 'sub_dur.attribute_value',
        4 => 'target_mc.attribute_value',
        5 => 'target_sc.attribute_value',
        6 => 'cum.total_max_utilization_amount',
        7 => 'users.username',
        8 => 'cum.created_at',
        9 => 'cum.status',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('component_utilization_mappings as cum')
            ->leftJoin('attribute_values as dur', 'cum.duration_id', '=', 'dur.id')
            ->leftJoin('attribute_values as sub_dur', 'cum.sub_duration_id', '=', 'sub_dur.id')
            ->leftJoin('attribute_values as target_mc', 'cum.target_major_component_id', '=', 'target_mc.id')
            ->leftJoin('attribute_values as target_sc', 'cum.target_sub_component_id', '=', 'target_sc.id')
            ->leftJoin('users', 'cum.created_by', '=', 'users.id')
            ->select(
                'cum.id',
                'cum.financial_year',
                'cum.duration_id',
                'cum.sub_duration_id',
                'cum.total_max_utilization_amount',
                'cum.remarks',
                'cum.status',
                'cum.created_at',
                'dur.attribute_value as duration_name',
                'sub_dur.attribute_value as sub_duration_name',
                'target_mc.attribute_value as target_major_component_name',
                'target_sc.attribute_value as target_sub_component_name',
                'users.username as created_by_name'
            );

        if (!empty($filters['financial_year'])) {
            $query->where('cum.financial_year', $filters['financial_year']);
        }

        if (!empty($filters['duration_id'])) {
            $query->where('cum.duration_id', $filters['duration_id']);
        }

        if (!empty($filters['sub_duration_id'])) {
            $query->where('cum.sub_duration_id', $filters['sub_duration_id']);
        }

        if (!empty($filters['target_major_component_id'])) {
            $query->where('cum.target_major_component_id', $filters['target_major_component_id']);
        }

        if (!empty($filters['target_sub_component_id'])) {
            $query->where('cum.target_sub_component_id', $filters['target_sub_component_id']);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('cum.financial_year', 'like', "%{$search}%")
                    ->orWhere('dur.attribute_value', 'like', "%{$search}%")
                    ->orWhere('sub_dur.attribute_value', 'like', "%{$search}%")
                    ->orWhere('target_mc.attribute_value', 'like', "%{$search}%")
                    ->orWhere('target_sc.attribute_value', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%")
                    ->orWhere('cum.total_max_utilization_amount', 'like', "%{$search}%");
            });
        }

        $orderColumn = $this->columns[$order] ?? 'cum.created_at';
        $query->orderBy($orderColumn, $dir);

        if ($page) {
            return $this->getDataTableResult(
                ComponentUtilizationMappingResource::collection($query->paginate($limit))
            );
        }

        return ComponentUtilizationMappingResource::collection($query->get());
    }
}
