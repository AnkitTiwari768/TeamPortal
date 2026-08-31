<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Workshop_Integeration;

use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Api\V1\Workshop_Integeration\WorkshopResource;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkshopService extends BaseService
{

    protected array $columns = [
        1 => 'w.event_title',
        2 => 'w.organiser_name',
        3 => 'r.name',
        3 => 'w.venue_address',
    ];

    public function getList()
    {
        /*$snpRoleId = DB::table('roles')->where('slug', 'snp')->value('id');

        $query = DB::table('workshops as w')
            ->leftJoin('roles as r', 'w.event_for', '=', 'r.id')
            ->select('w.*', 'r.name as event_for_name');

        if (hasRole('msme')) {
            $query->where('w.event_for', $snpRoleId);
        }*/

            $query = DB::table('workshops as w')
        ->leftJoin('roles as r', function ($join) {
            $join->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(r.id))");
        })
        ->select('w.*', DB::raw('GROUP_CONCAT(r.name) as event_for_name'))
        ->groupBy('w.id');

        return $query->orderBy('w.created_at', 'desc')->get();
    }


    public function storeEvent(array $payload, ?string $eventId = null): bool
    {
        // dd($payload['uploaded_ids']);
        $schedules = [];

        foreach ($payload['start_date'] as $key => $start) {
            $schedules[] = [
                'start_date' => $start,
                'end_date'   => $payload['end_date'][$key],
                'start_time' => $payload['start_time'][$key] ?? null,
            ];
        }

        $eventData = [
            'event_title' => $payload['event_title'],
            'event_for' => $payload['event_for'],
            'organiser_name' => $payload['organiser_name'],
            'remark' => $payload['remark'] ?? null,
            'venue_address' => $payload['venue_address'] ?? null,
            'schedules' => json_encode($schedules),
            'event_description' => $payload['event_description'] ?? null,
            'state_id' => $payload['state_id'] ?? null,
            'district_id' => $payload['district_id'] ?? null,
            'pincode' => $payload['pincode'] ?? null,
            'latitude' => $payload['latitude'] ?? null,
            'longitude' => $payload['longitude'] ?? null,
            'uploaded_ids' => $payload['uploaded_ids'] ?? null,
        ];

        // dd($eventData);

        if (!$eventId) {
            $eventData['created_at'] = currentDateTime();
            $eventData['created_by'] = AuthId();

            $eventData['updated_at'] = currentDateTime();
            $eventData['updated_by'] = AuthId();

            return (bool) Workshop::create($eventData);
        }

        $eventData['updated_at'] = currentDateTime();
        $eventData['updated_by'] = AuthId();

        $Workshop = Workshop::findOrFail($eventId);

        return (bool) $Workshop->fill($eventData)->save();
    }



    public function getEventData(string $eventId)
    {
        return Workshop::select('*')->find($eventId);
    }

    public function getUploadImageData($id)
    {

        $workshop = \DB::table('workshops')->where('id', $id)->first();

        if (!$workshop || !$workshop->uploaded_ids) {
            return collect();
        }

        $ids = explode(',', $workshop->uploaded_ids);

        return \DB::table('file_uploads')->whereIn('id', $ids)->get();
    }

    public function cardData()
    {
        $msmeRoleId = DB::table('roles')->where('slug', 'msme')->value('id');
        return DB::table('workshops as w')
            //->leftJoin('roles as r', 'w.event_for', '=', 'r.id')
            ->leftJoin('roles as r', function ($join) {
                $join->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(r.id))");
            })
            ->leftJoin('states as s', DB::raw('w.state_id COLLATE utf8mb4_unicode_ci'), '=', DB::raw('s.id COLLATE utf8mb4_unicode_ci'))
            ->leftJoin('file_uploads as fu','fu.id','=','w.uploaded_ids')
            ->select('w.*', DB::raw('GROUP_CONCAT(r.name) as event_for_name'), 's.name as state_name','fu.file_path','fu.file_system_name')
            ->whereRaw("JSON_CONTAINS(w.event_for, JSON_QUOTE(?))", [$msmeRoleId])
            ->groupBy('w.id')
            ->orderBy('w.created_at', 'desc')
            ->get()
            ->map(fn($item) => $this->formatWorkshop($item));
    }

    public function formatWorkshop($item)
    {
        $schedule = json_decode($item->schedules, true);

        if (is_string($schedule)) {
            $schedule = json_decode($schedule, true);
        }

        if (!empty($schedule) && is_array($schedule)) {

            $startDates = [];
            $endDates   = [];

            foreach ($schedule as $row) {
                $startDates[] = Carbon::createFromFormat('d-m-Y', $row['start_date']);
                $endDates[]   = Carbon::createFromFormat('d-m-Y', $row['end_date']);
            }

            $start = collect($startDates)->min();
            $end   = collect($endDates)->max();

            $item->start_date = $start->format('d-m-Y');
            $item->end_date   = $end->format('d-m-Y');
            $item->duration   = $start->diffInDays($end) + 1;
        }

        return $item;
    }

    public function getScheduleSummary($schedules)
    {
        $schedules = is_string($schedules) ? json_decode($schedules, true) : $schedules;

        if (empty($schedules)) {
            return [
                'start_date' => null,
                'end_date'   => null,
                'duration'   => 0,
                'schedules'  => []
            ];
        }

        $startDates = array_column($schedules, 'start_date');
        $endDates   = array_column($schedules, 'end_date');

        $start = Carbon::createFromFormat('d-m-Y', min($startDates));
        $end   = Carbon::createFromFormat('d-m-Y', max($endDates));

        return [
            'start_date' => $start->format('d-m-Y'),
            'end_date'   => $end->format('d-m-Y'),
            'duration'   => $start->diffInDays($end) + 1,
            'schedules'  => $schedules
        ];
    }
}
