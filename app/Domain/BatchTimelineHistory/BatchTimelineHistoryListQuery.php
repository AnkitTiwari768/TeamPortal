<?php

declare(strict_types=1);

namespace App\Domain\BatchTimelineHistory;

use App\Traits\DataTable;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class BatchTimelineHistoryListQuery
{
    use DataTable;

    protected array $columns = [
        1 => 'tss.organization_name',
        2 => 'bt.batch_number',
        3 => 'bt.subject',
        4 => 'bt.comments',
        5 => 'bt.status',
        6 => 'bt.created_at',
        7 => 'bt.created_by_role',
    ];

    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = $this->baseQuery($search, $filters ?? []);

        $query->orderBy($order, $dir)->orderBy('bt.batch_id', 'desc');

        if ($page) {
            return $this->getDataTableResult(
                BatchTimelineHistoryResource::collection($query->paginate($limit))
            );
        }

        return BatchTimelineHistoryResource::collection($query->get());
    }

    /**
     * Filtered/searched dataset (no pagination) for the Excel/PDF exports, so a
     * download always reflects the same search & filters as the on-screen list.
     */
    public function getExportRows(?int $limit = null)
    {
        [, , , $search, , $filters] = $this->getDataTableParams();

        $query = $this->baseQuery($search, $filters ?? []);

        $query->orderBy('bt.batch_id')->orderBy('bt.created_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getExportRowCount(): int
    {
        [, , , $search, , $filters] = $this->getDataTableParams();

        return $this->baseQuery($search, $filters ?? [])->count();
    }

    /**
     * Distinct status values already recorded on batch_timelines, used to
     * populate the Status filter dropdown.
     */
    public function getStatusOptions(): array
    {
        return DB::table('batch_timelines')
            ->whereNotNull('status')
            ->where('status', '!=', '')
            ->distinct()
            ->orderBy('status')
            ->pluck('status', 'status')
            ->toArray();
    }

    private function baseQuery(?string $search, array $filters): Builder
    {
        $query = DB::table('batch_timelines as bt')
            ->join('dy_batches as db', 'db.id', '=', 'bt.batch_id')
            ->join('users as u', 'u.id', '=', 'db.created_by')
            ->join('team_snp_scheme as tss', 'tss.user_id', '=', 'u.id')
            ->select(
                'bt.id',
                'bt.batch_id',
                'tss.organization_name',
                'bt.batch_number',
                'bt.subject',
                'bt.comments as action',
                'bt.status',
                'bt.created_at',
                'bt.created_by_role'
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('tss.organization_name', 'like', "%{$search}%")
                    ->orWhere('bt.batch_number', 'like', "%{$search}%")
                    ->orWhere('bt.subject', 'like', "%{$search}%")
                    ->orWhere('bt.comments', 'like', "%{$search}%")
                    ->orWhere('bt.status', 'like', "%{$search}%")
                    ->orWhere('bt.created_by_role', 'like', "%{$search}%")
                    ->orWhereRaw("DATE_FORMAT(bt.created_at, '%d-%m-%Y') like ?", ["%{$search}%"]);
            });
        }

        if (!empty($filters['from_date'])) {
            $from = Carbon::createFromFormat('d-m-Y', $filters['from_date'])->format('Y-m-d');
        }

        if (!empty($filters['to_date'])) {
            $to = Carbon::createFromFormat('d-m-Y', $filters['to_date'])->format('Y-m-d');
        }

        if (!empty($from) && !empty($to)) {
            $query->whereBetween('bt.created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
        }

        if (!empty($filters['status'])) {
            $query->where('bt.status', $filters['status']);
        }

        return $query;
    }
}
