<?php 

namespace App\Web\Menu;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'title_en' => $this->title_en,
			'title_mr' => $this->title_mr,
            'slug_en' => $this->slug_en,
			'slug_mr' => $this->slug_mr,
			'show_in' => $this->show_in,
            'sort_order' => $this->sort_order,
			'sub_menu' => $this->sub_menu,
            'template' => $this->template,
			'fmenu_category' => $this->fmenu_category,
			'is_active' => $this->is_active
        ];
    }
}