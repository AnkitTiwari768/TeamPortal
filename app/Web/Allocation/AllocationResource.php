<?php

declare(strict_types=1);

namespace App\Web\Allocation;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * AllocationResource
 *
 * Transforms an allocation_header row for DataTable / API responses.
 */
class AllocationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'financial_year'        => $this->financial_year,
            'duration_id'           => $this->duration_id,
            'duration_label'        => $this->duration_label ?? $this->duration_id,
            'sub_duration_id'       => $this->sub_duration_id,
            'sub_duration_label'    => $this->sub_duration_label ?? null,
            'sanction_order_number' => $this->sanction_order_number,
            'sanction_order_date'   => $this->sanction_order_date_fmt ?? $this->sanction_order_date,
            'total_amount'          => $this->total_amount ?? 0,
            'remarks'               => $this->remarks,
            'document_path'         => $this->document_path,
            'created_at'            => $this->created_at,
        ];
    }
}
