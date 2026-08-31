<?php 

declare(strict_types=1);

namespace App\Web\Components;

use Illuminate\Http\Resources\Json\JsonResource;

class ComponentsResource extends JsonResource
{
    public function toArray($request)
    {
		//return parent::toArray($request);
        return [
            'id' => $this->id,
			'major_component_id' => $this->major_component_id,
			'major_component_name' => $this->major_component_name,
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