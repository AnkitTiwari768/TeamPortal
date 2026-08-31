<?php

declare(strict_types=1);

namespace App\Domain\FundFlow;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ComponentUtilizationMappingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                           => $this->id,
            'financial_year'               => $this->financial_year,
            'duration_name'                => $this->duration_name ?? '-',
            'sub_duration_name'            => $this->sub_duration_name ?? '-',
            'target_major_component_name'  => $this->target_major_component_name ?? '-',
            'target_sub_component_name'    => $this->target_sub_component_name ?? '-',
            'total_max_utilization_amount' => $this->total_max_utilization_amount,
            'remarks'                      => $this->remarks,
            'status'                       => $this->status ?? 'COMPLETED',
            'created_by_name'              => $this->created_by_name ?? '-',
            'created_at'                   => $this->created_at ? Carbon::parse($this->created_at)->format('d-m-Y') : '-',
        ];
    }
}
