<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ListFundCarryForwardAction
{
    use DataTable;

    protected array $columns = [
        1 => 'fcf.financial_year',
        2 => 'from_sub_duration_av.attribute_value',
        3 => 'to_sub_duration_av.attribute_value',
        4 => 'fcf.total_amount',
        5 => 'users.username',
        6 => 'fcf.created_at',
        7 => 'fcf.status',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = DB::table('fund_carry_forwards as fcf')
            ->leftJoin('attribute_values as from_duration_av', 'fcf.from_duration_id', '=', 'from_duration_av.id')
            ->leftJoin('attribute_values as from_sub_duration_av', 'fcf.from_sub_duration_id', '=', 'from_sub_duration_av.id')
            ->leftJoin('attribute_values as to_duration_av', 'fcf.to_duration_id', '=', 'to_duration_av.id')
            ->leftJoin('attribute_values as to_sub_duration_av', 'fcf.to_sub_duration_id', '=', 'to_sub_duration_av.id')
            ->leftJoin('users', 'fcf.created_by', '=', 'users.id')
            ->select(
                'fcf.id',
                'fcf.financial_year',
                'fcf.carry_forward_date',
                'fcf.total_amount',
                'fcf.remarks',
                'fcf.status',
                'fcf.created_at',
                'from_duration_av.attribute_value as from_duration_name',
                'from_sub_duration_av.attribute_value as from_sub_duration_name',
                'to_duration_av.attribute_value as to_duration_name',
                'to_sub_duration_av.attribute_value as to_sub_duration_name',
                'users.username as created_by_name'
            );

        if (!empty($filters['financial_year'])) {
            $query->where('fcf.financial_year', $filters['financial_year']);
        }

        if (!empty($filters['from_duration_id'])) {
            $query->where('fcf.from_duration_id', $filters['from_duration_id']);
        }

        if (!empty($filters['from_sub_duration_id'])) {
            $query->where('fcf.from_sub_duration_id', $filters['from_sub_duration_id']);
        }

        if (!empty($filters['to_sub_duration_id'])) {
            $query->where('fcf.to_sub_duration_id', $filters['to_sub_duration_id']);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('fcf.financial_year', 'like', "%$search%")
                    ->orWhere('from_sub_duration_av.attribute_value', 'like', "%$search%")
                    ->orWhere('to_sub_duration_av.attribute_value', 'like', "%$search%")
                    ->orWhere('users.username', 'like', "%$search%")
                    ->orWhere('fcf.total_amount', 'like', "%$search%");
            });
        }

        $orderColumn = $this->columns[$order] ?? 'fcf.created_at';
        $query->orderBy($orderColumn, $dir);

        if ($page) {
            return $this->getDataTableResult(
                FundCarryForwardResource::collection($query->paginate($limit))
            );
        }

        return FundCarryForwardResource::collection($query->get());
    }
}
