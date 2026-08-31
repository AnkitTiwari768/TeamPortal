<?php

declare(strict_types=1);

namespace App\Domain\FundAllocation;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FundAllocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'financial_year'         => $this->financial_year,
            'duration'               => $this->duration_name ?? '-',
            'sub_duration'           => $this->sub_duration_name ?? '-',
            'sanction_order_no'      => $this->sanction_order_number,
            'sanction_order_date'    => $this->sanction_order_date ? Carbon::parse($this->sanction_order_date)->format('d-m-Y') : '-',
            'total_fresh_amount'     => $this->total_fresh_amount ?? 0,
            'total_allocated_amount' => $this->total_allocated_amount ?? 0,
            'total_available_amount' => $this->total_available_amount ?? 0,
            'remaining_amount'       => $this->remaining_amount ?? 0,
            'created_at'             => $this->created_at ? Carbon::parse($this->created_at)->format('d-m-Y') : '-',
        ];
    }
}
