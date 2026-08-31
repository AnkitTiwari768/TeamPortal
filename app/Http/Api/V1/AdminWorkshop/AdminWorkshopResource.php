<?php

namespace App\Http\Api\V1\AdminWorkshop;

use Illuminate\Http\Resources\Json\JsonResource;

class AdminWorkshopResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'financial_year' => $this->financial_year,
            'duration_name' =>  $this->duration_name ?? $this->duration ?? '-',
            'sub_duration'  =>  $this->sub_duration,
            'workshop_date'  => $this->workshop_date,
            'state_name'     => $this->state_name ?? '-',
            'district_name'  => $this->district_name ?? '-',
            'total_expense'  => $this->total_expense ?? 0,
            'created_at'     => $this->created_at,
        ];
    }
}
