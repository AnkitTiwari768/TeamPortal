<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\District;

use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
			'country_id' => $this->country_id,
			'country_name' => $this->country_name,
			'state_id' => $this->state_id,
			'state_name' => $this->state_name,
            'name' => ucwords(strtolower($this->name)),
            'slug' => $this->slug,
			'code' => $this->code,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}