<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use App\Traits\DataTable;
use App\Web\Workshop\WorkshopResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ListExecutedWorkshopAction
{
    use DataTable;

    protected array $columns = [
        1 => 'w.event_title',
        2 => 'o_r.name',
        3 => 'r.name',
        4 => 'w.venue_address',
    ];

    /**
     * Execute the list workshop action.
     *
     * @return array|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function execute()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

         // Request se direct values lein (fallback)
        $request = request();
        if (empty($filters['from_dates']) && $request->has('from_dates')) {
            $filters['from_dates'] = $request->input('from_dates');
        }
        if (empty($filters['to_dates']) && $request->has('to_dates')) {
            $filters['to_dates'] = $request->input('to_dates');
        }
        if (empty($filters['status']) && $request->has('status')) {
            $filters['status'] = $request->input('status');
        }
        $query = DB::table('workshops as w')
            ->leftJoin('roles as r', function ($join) {
                $join->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(r.id))");
            })
            ->leftJoin('states as s', DB::raw('w.state_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('locations as l', DB::raw('w.district_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('l.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('sub_districts as sd', 'w.sub_district_id', '=', 'sd.id')
            ->leftJoin('roles as o_r', DB::raw('w.organiser_name COLLATE utf8mb4_unicode_ci'), '=', DB::raw('o_r.id COLLATE utf8mb4_unicode_ci'))
            ->select(
                'w.*',
                'sd.name as sub_distict_name',
                's.name as state_name',
                'l.name as district_name',
                'o_r.name as org_name',
                DB::raw('GROUP_CONCAT(r.name) as event_for_name')
            )
            ->groupBy('w.id'); 

        //$query->where('w.created_by', authId());
        $query->where('is_executed_workshop', true);
        $query->where(function($q) {
                $q->whereNull('w.status')
                ->orWhere('w.status', '!=', 'Cancelled');
            });
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('w.event_title', 'like', "%$search%")
                    ->orWhere('o_r.name', 'like', "%$search%") // Search organiser name
                    ->orWhere('r.name', 'like', "%$search%")   // Search event for role
                    ->orWhere('w.venue_address', 'like', "%$search%") // Search venue
                    ->orWhere('w.status', 'like', "%$search%") // Search status
                    ->orWhere('w.schedules', 'like', "%$search%"); // Search start/end dates stored in schedules JSON
            });
        }

        $from = !empty($filters['from_dates']) ? $filters['from_dates'] : null;
        $to = !empty($filters['to_dates']) ? $filters['to_dates'] : null;

        if ($from && $to) {
            $query->whereRaw("
                (
                    STR_TO_DATE(
                        JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].start_date')),
                        '%d-%m-%Y'
                    ) <= STR_TO_DATE(?, '%d-%m-%Y')
                )
                AND
                (
                    STR_TO_DATE(
                        JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].end_date')),
                        '%d-%m-%Y'
                    ) >= STR_TO_DATE(?, '%d-%m-%Y')
                )
            ", [$to, $from]);
        }

        if (!empty($filters['event_for'])) {
            $query->whereRaw("
                JSON_CONTAINS(w.event_for, JSON_QUOTE(?))
            ", [$filters['event_for']]);
        }

        
        // ✅ EXPLICIT STATUS (Completed / Cancelled)
        if (!empty($filters['status'])) {
            $statusValue = $filters['status'];
            if (in_array($statusValue, ['Completed', 'Cancelled'])) {
                $query->where('w.status', $statusValue);
            }
        }
        if (!empty($filters['state_list'])) {
            $query->where('w.state_id', $filters['state_list']);
        }

        $status = $filters['eventStatus'] ?? null;
        $today = now()->format('d-m-Y');

        if ($status === 'upcoming') {
            $query->whereRaw("
                STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].start_date')), '%d-%m-%Y') > STR_TO_DATE(?, '%d-%m-%Y')
            ", [$today]);
        }

        if ($status === 'completed') {
            $query->whereRaw("
                STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].end_date')), '%d-%m-%Y') < STR_TO_DATE(?, '%d-%m-%Y')
            ", [$today]);
        }

        if ($status === 'ongoing') {
            $query->whereRaw("
                STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].start_date')), '%d-%m-%Y') <= STR_TO_DATE(?, '%d-%m-%Y')
                AND
                STR_TO_DATE(JSON_UNQUOTE(JSON_EXTRACT(w.schedules, '$[0].end_date')), '%d-%m-%Y') >= STR_TO_DATE(?, '%d-%m-%Y')
            ", [$today, $today]);
        }

        if (hasRole('snp')) {
            $snpRoleId = DB::table('roles')->where('slug', 'snp')->value('id');
            $query->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$snpRoleId]);
        } elseif (hasRole('bnp')) {
            $bnpRoleId = DB::table('roles')->where('slug', 'bnp')->value('id');
            $query->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$bnpRoleId]);
        } elseif (hasRole('lsp')) {
            $lspRoleId = DB::table('roles')->where('slug', 'lsp')->value('id');
            $query->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$lspRoleId]);
        }

        $orderColumn = $this->columns[$order] ?? 'w.created_at';
        $query->orderBy($orderColumn, $dir);

        if ($page) {
            return $this->getDataTableResult(
                WorkshopResource::collection($query->paginate($limit))
            );
        }

        return WorkshopResource::collection($query->get());
    }
}
