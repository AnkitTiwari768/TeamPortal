<?php 

declare(strict_types=1);

namespace App\Web\MajorComponents;

use Illuminate\Http\Resources\Json\JsonResource;

class MajorComponentsResource extends JsonResource
{
    public function toArray($request)
    {
		return parent::toArray($request);
        //return [
          //  'id' => $this->id, 
            //'name' => $this->name,
            //'slug' => $this->slug,
			//'iso2_code' => $this->iso2_code,
            //'iso3_code' => $this->iso3_code,
            //'status' => $this->status,
            //'created_by' => $this->created_by,
            //'updated_by' => $this->updated_by,
            //'created_at' => $this->created_at,
            //'updated_at' => $this->updated_at,
        //];
    }
}