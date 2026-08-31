<?php

namespace App\Web\FlowManagement;

use Illuminate\Http\Resources\Json\JsonResource;

class FlowManagementResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->f_id,
            'workflow_type' => $this->workflow_type,
            'status' => $this->status,
        ];
    }
}
