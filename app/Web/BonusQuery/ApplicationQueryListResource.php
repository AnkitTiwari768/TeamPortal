<?php

namespace App\Web\BonusQuery;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationQueryListResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'query_number' => $this->query_number,
            'team_id' => $this->team_id,
            'udyam_no' => $this->udyam_no,
            'enterprise_name' => $this->enterprise_name,
            'status' => QueryStatus::getName((int) $this->status),
            'status_code' => (int) $this->status,
            'remark' => $this->remark,
            'raised_at' => date('Y-m-d H:i', strtotime($this->raised_at)),
            'raised_by' => $this->raised_by,
        ];
    }
}
