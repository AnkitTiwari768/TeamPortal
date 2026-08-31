<?php 

declare(strict_types=1);

namespace App\Web\Language;

use Illuminate\Http\Resources\Json\JsonResource;

class LanguageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,           
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
          
        ];
    }
}