<?php

declare(strict_types=1);

namespace App\Web\Workshop;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $scheduleData = $this->parseSchedules($this->schedules);

        return [
            'id'                => $this->id,
            'event_title'       => $this->event_title,
            'event_for'         => $this->event_for_name,
            'organiser_name'    => $this->org_name,
            'organizer_name'    => $this->org_name,
            'venue_address'     => $this->venue_address,
            'remark'            => $this->remark,
            'status'            => $this->status,
            'event_description' => $this->event_description,

            'start_date'        => $scheduleData['start_date'],
            'end_date'          => $scheduleData['end_date'],
            'duration'          => $scheduleData['duration'],
            'schedules'         => $scheduleData['schedules'],

            'state_id'          => $this->state_id,
            'district_id'       => $this->district_id,
            'state_name'        => $this->state_name,
            'district_name'     => $this->district_name,
            'sub_distict_name'  => $this->sub_distict_name,
            'pincode'           => $this->pincode,
            'latitude'          => $this->latitude,
            'longitude'         => $this->longitude,
            'uploaded_ids'      => $this->uploaded_ids,

            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }

    /**
     * Parse schedules JSON/array to extract start_date, end_date, duration and decoded schedules.
     *
     * @param mixed $schedules
     * @return array{start_date: ?string, end_date: ?string, duration: ?int, schedules: array}
     */
    private function parseSchedules(mixed $schedules): array
    {
        if (is_string($schedules)) {
            $schedules = json_decode($schedules, true);
        }

        if (!is_array($schedules) || empty($schedules)) {
            return [
                'start_date' => null,
                'end_date'   => null,
                'duration'   => null,
                'schedules'  => [],
            ];
        }

        $startDates = [];
        $endDates   = [];

        foreach ($schedules as $row) {
            if (!empty($row['start_date'])) {
                $startDates[] = Carbon::createFromFormat('d-m-Y', $row['start_date']);
            }
            if (!empty($row['end_date'])) {
                $endDates[] = Carbon::createFromFormat('d-m-Y', $row['end_date']);
            }
        }

        if (empty($startDates) || empty($endDates)) {
            return [
                'start_date' => null,
                'end_date'   => null,
                'duration'   => null,
                'schedules'  => $schedules,
            ];
        }

        $start = collect($startDates)->min();
        $end   = collect($endDates)->max();

        return [
            'start_date' => $start->format('d-m-Y'),
            'end_date'   => $end->format('d-m-Y'),
            'duration'   => $start->diffInDays($end) + 1,
            'schedules'  => $schedules,
        ];
    }
}