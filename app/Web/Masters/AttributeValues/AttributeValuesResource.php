<?php 

declare(strict_types=1);

namespace App\Web\Masters\AttributeValues;
use Illuminate\Http\Resources\Json\JsonResource;

class AttributeValuesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
			'attribute_id' => $this->attribute_id,                
			'attribute_value' => $this->attribute_value,
			'code' => $this->code,
			'status' => $this->status,
            'sort_order' => $this->sort_order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}