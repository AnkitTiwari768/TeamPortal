<?php

declare(strict_types=1);

namespace App\Domain\FundCarryForward;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundCarryForwardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'financial_year'         => $this->financial_year,
            'from_duration'          => $this->from_duration_name ?? '-',
            'from_sub_duration'      => $this->from_sub_duration_name ?? '-',
            'to_duration'            => $this->to_duration_name ?? '-',
            'to_sub_duration'        => $this->to_sub_duration_name ?? '-',
            'carry_forward_date'     => $this->carry_forward_date ? Carbon::parse($this->carry_forward_date)->format('d-m-Y') : '-',
            'total_amount'           => $this->total_amount,
            'remarks'                => $this->remarks,
            'status'                 => $this->status,
            'created_by_name'        => $this->created_by_name ?? '-',
            'created_at'             => $this->created_at ? Carbon::parse($this->created_at)->format('d-m-Y') : '-',
        ];
    }
}
