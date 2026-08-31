<?php 

namespace App\Web\Page;

use Illuminate\Http\Resources\Json\JsonResource;
use DB;
class PageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
			//'menu_title' => $this->parent_child($this->menu_id),
			'menu_title_en' => $this->menu_title_en,
			'menu_title_mr' => $this->menu_title_mr,
            'title_en' => $this->title_en,
			'title_mr' => $this->title_mr,
            'slug_en' => $this->slug_en,
			'slug_mr' => $this->slug_mr,
			'description_en' => $this->description_en,
            'description_mr' => $this->description_mr,
			'is_active' => $this->is_active
        ];
    }
	
	 /*public function parent_child($menu_id){
		$query=DB::table('menus');
		$query->where('id',$menu_id);
	    $result=$query->get()->toArray();
	    $menus=json_decode(json_encode($result), true);
		$elements = array();
		foreach($menus as $menu) {
			$elements[] = $menu['title_en'];
		}
		return implode('/', $elements);
	 }*/

}