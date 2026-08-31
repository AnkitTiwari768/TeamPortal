<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

use App\Traits\DataTable;
use Illuminate\Support\Facades\DB;

class ApplicationQueryListAction
{
    use DataTable;

    protected array $columns = [
        1 => 'ms.team_id',
        2 => 'bq.query_number',
        3 => 'bq.raised_at',
        4 => 'u.first_name',
        5 => 'bq.status',
    ];

    public function execute(?string $id = null)
    {
        [$limit, $order, $dir, $search, $page] = $this->getDataTableParams();

        $query = DB::table('bonus_queries as bq')
            ->select([
                'bq.id',
                'bq.query_number',
                'bq.status',
                'bq.remark',
                'bq.raised_at',
                'u.first_name as raised_by',
                'ms.team_id',
                'ms.udyam_no',
                'ms.enterprise_name',
            ])
            ->join('team_msme_schemes as ms', 'ms.id', '=', 'bq.msme_id')
            ->join('users as u', 'u.id', '=', 'bq.raised_by');

        // 🔹 optional: claim type filter
        // if (!empty($claimTypeId)) {
        //     $query->where('bq.claim_type_id', $claimTypeId);
        // }

        // 🔹 role based (SNP sees only own queries)
        if (hasRole('snp')) {
            $query->where('bq.raised_by', auth()->id());
        }

        // 🔍 search
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('bq.query_number', 'like', "%{$search}%")
                  ->orWhere('ms.team_id', 'like', "%{$search}%")
                  ->orWhere('ms.udyam_no', 'like', "%{$search}%")
                  ->orWhere('u.first_name', 'like', "%{$search}%");
            });
        }

        // ↕️ order
        $query->orderBy($order, $dir);

        // 📄 datatable response
        if ($page) {
            return $this->getDataTableResult(
                ApplicationQueryListResource::collection(
                    $query->paginate($limit)
                )
            );
        }

        return ApplicationQueryListResource::collection($query->get());
    }
}
