<?php

declare(strict_types=1);

namespace App\Domain\BatchTimelineHistory;

use Illuminate\Http\Resources\Json\JsonResource;

class BatchTimelineHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'batch_id' => $this->batch_id,
            'organization_name' => $this->organization_name,
            'batch_number' => $this->batch_number,
            'subject' => $this->subject,
            'action' => $this->action,
            'status' => $this->status,
            'created_at' => $this->created_at ? date('d-m-Y H:i:s', strtotime($this->created_at)) : null,
            'created_by_role' => $this->created_by_role,
        ];
    }
}
