<?php 

namespace App\Web\Slider;

use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title_en' => $this->title_en,
			'title_mr' => $this->title_mr,
            'slug_en' => $this->slug_en,
			'slug_mr' => $this->slug_mr,
			'type' => $this->type,
			'url' => $this->url,
            'sort_order' => $this->sort_order,
			'images' => $this->images,
			'is_active' => $this->is_active
        ];
    }
}