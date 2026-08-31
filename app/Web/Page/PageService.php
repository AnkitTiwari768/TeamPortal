<?php 
declare (strict_types = 1);

namespace App\Web\Page;

use App\Core\BaseService;
use App\Web\Page\Page as Model;
use App\Web\Page\PageResource as Resource;
use Illuminate\Support\Str;
use DB;


class PageService extends BaseService 
{
    protected static $model = Model::class;
    protected static $resource  = Resource::class;

    protected $columns = [
        1 => 'cms_menus.title_en',
		2 => 'cms_menus.title_mr',
		3 => 'cms_pages.title_en',
		4 => 'cms_pages.title_mr',
		5 => 'cms_pages.description_en',
		6 => 'cms_pages.description_mr',
        //7 => 'cms_pages.is_active'
    ];

    public function getDataTableList()
    { 
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        $query = Model::select('cms_pages.*', 'cms_menus.title_en as menu_title_en','cms_menus.title_mr as menu_title_mr')
            ->join('cms_menus', 'cms_pages.menu_id', '=', 'cms_menus.id');
		
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			if ($this->english_characters($search)) {
				$query->where('cms_menus.title_en','LIKE',"%$search%")
				->orWhere('cms_pages.title_en','LIKE',"%$search%")
				->orWhere('cms_pages.description_en','LIKE',"%$search%")
				->orWhere(DB::raw("(CASE WHEN cms_pages.is_active=". config('constant.ACTIVE') .' THEN "'. __('message.active') .'" ELSE "'. __('message.in_active') .'" END)'), 'LIKE', $search . '%');
				//$query->orWhereRaw("IF(cms_pages.is_active = 1 ,'Active','In-active') LIKE '%$search%'");			
			}else{
				$query->where('cms_menus.title_mr','LIKE',"%$search%");
				$query->orWhere('cms_pages.title_mr','LIKE',"%$search%"); 
				$query->orWhere('cms_pages.description_mr','LIKE',"%$search%");
			}
			//$query->orWhereRaw($this->datatable_isactive("is_active"). "LIKE '%$search%'");
        }

        $query->orderBy($order, $dir); 
        if (isset($page) && !empty($page)) {
            return $this->getDataTableResult(
                Resource::collection(
                    $query->paginate($limit)
                )
            );
        }

        return Resource::collection($query->get());
    }


    public function save($payload, $id = null)
    {
        if ($payload['title_en']) {
            $payload['slug_en'] = Str::slug($payload['title_en']);
        }
		
		if ($payload['title_mr']) {
            $payload['slug_mr'] = \App\Web\Menu\MenuHelperService::generateHindiSlug($payload['title_mr']);
        }
		 
		
		$payload['description_en'] =html_entity_decode($payload['description_en']);
		$payload['description_mr'] =html_entity_decode($payload['description_mr']);

        if ($id) {
            $payload['updated_by'] = AuthId();
            $menu = Model::findOrFail($id);
            $menu->update($payload);
            return $menu;
        } else {
            $payload['id'] = (string) Str::uuid(); // Generate and assign UUID
            $payload['created_by'] = AuthId();
            return Model::create($payload);
        }

        //return ($id) ? parent::update($payload, $id) : parent::create($payload);
    }

     public function findById($id)
    {
        return Model::findOrFail($id);
    }
}