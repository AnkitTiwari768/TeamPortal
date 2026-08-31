<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\SubDistrict;

use Illuminate\Http\Resources\Json\JsonResource;

class SubDistrictResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
			'district_id' => $this->district_id,
			'district_code' => $this->district_code,
			//'state_id' => $this->state_id,
			//'state_name' => $this->state_name,
            'name' => ucwords(strtolower($this->name)),
            'slug' => $this->slug,
			'code' => $this->code,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}