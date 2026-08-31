<?php 

declare(strict_types=1);

namespace App\Web\SubComponents;
use Illuminate\Http\Resources\Json\JsonResource;

class SubComponentsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
			'major_component_id' => $this->major_component_id,
			'major_component_name' => $this->major_component_name,
			'component_id' => $this->component_id,
			'component_name' => $this->component_name,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}