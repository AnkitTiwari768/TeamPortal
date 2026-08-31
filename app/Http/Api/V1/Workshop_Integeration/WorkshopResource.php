<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Workshop_Integeration;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkshopResource extends JsonResource
{
    public function toArray($request)
    {
         $schedules = $this->schedules;

    // decode schedules safely
    if (is_string($schedules)) {
        $schedules = json_decode($schedules, true);
    }

    if (is_string($schedules)) {
        $schedules = json_decode($schedules, true);
    }

    $start_date = null;
    $end_date   = null;
    $duration   = null;

    if (!empty($schedules) && is_array($schedules)) {

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

        if ($startDates && $endDates) {

            $start = collect($startDates)->min();
            $end   = collect($endDates)->max();

            $start_date = $start->format('d-m-Y');
            $end_date   = $end->format('d-m-Y');
            $duration   = $start->diffInDays($end) + 1;
        }
    }
        
        return [
            'id' => $this->id,
            'event_title' => $this->event_title,
            'event_for' => $this->event_for_name,
            'organiser_name' => $this->organiser_name,

            'venue_address' => $this->venue_address,
            'remark' => $this->remark,
            'event_description' => $this->event_description,

            'start_date' => $start_date,
            'end_date' => $end_date,
            'duration' => $duration,

            'schedules' => $this->schedules ? json_decode($this->schedules, true) : [],

            'state_id' => $this->state_id,
            'district_id' => $this->district_id,
            'pincode' => $this->pincode,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'uploaded_ids' => $this->uploaded_ids,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}