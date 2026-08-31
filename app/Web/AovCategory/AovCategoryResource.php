<?php 

declare(strict_types=1);

namespace App\Web\AovCategory;

use Illuminate\Http\Resources\Json\JsonResource;

class AovCategoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'ondc_domain_id' => $this->ondc_domain_id,
            'aov_grouping_type' => $this->aov_grouping_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}