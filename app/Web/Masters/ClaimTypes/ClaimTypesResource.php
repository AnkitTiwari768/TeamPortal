<?php 

declare(strict_types=1);

namespace App\Web\Masters\ClaimTypes;

use Illuminate\Http\Resources\Json\JsonResource;

class ClaimTypesResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug, 
            'short_name' => $this->short_name,        
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          
        ];
    }
}