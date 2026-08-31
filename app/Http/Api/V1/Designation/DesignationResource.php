<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Designation;

use Illuminate\Http\Resources\Json\JsonResource;

class DesignationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'department_name' => $this->department_name,
            'slug' => $this->slug,
            'status' => $this->status,
			'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}