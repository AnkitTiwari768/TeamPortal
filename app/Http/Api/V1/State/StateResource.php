<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\State;

use Illuminate\Http\Resources\Json\JsonResource;

class StateResource extends JsonResource
{
    public function toArray($request)
    {
		//return parent::toArray($request);
        return [
            'id' => $this->id,
			'country_id' => $this->country_id,
			'country_name' => $this->country_name,
            'is_state_premission_enabled' => $this->is_state_premission_enabled,
            'name' => $this->name,
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