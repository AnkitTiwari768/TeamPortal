<?php 
declare (strict_types = 1);

namespace App\Web\Menu;

use App\Core\BaseService;
use App\Web\Menu\Menu as Model;
use App\Web\Menu\MenuResource as Resource;
use Illuminate\Support\Str;
use DB;


class MenuService extends BaseService 
{
    protected static $model = Model::class;
    protected static $resource  = Resource::class;

    protected $columns = [
        1 => 'title_en',
		2 => 'title_en',
		3 => 'title_mr',
		4 => 'sort_order',
		5 => 'template',
        6 => 'is_active'
    ];

    public function getDataTableList()
    {
        /*[
            'search' => $search,
            'order' => $order,
            'limit' => $limit,
            'dir' => $dir,
            'page' => $page
        ] = $this->getDataTableParams();*/
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();
        //$query = Model::query();
		$query =Model::select('cms_menus.*');
        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			if ($this->english_characters($search)) {
				$template=2;
				$query->where('title_en','LIKE',"%$search%")
				->orWhere('sort_order','LIKE',"%$search%")
				->orWhere(DB::raw("(CASE WHEN cms_menus.template=".$template.' THEN "Dynamic Page" ELSE "No" END)'), 'LIKE', $search . '%')
				->orWhere(DB::raw("(CASE WHEN cms_menus.is_active=". config('constant.ACTIVE') .' THEN "'. __('message.active') .'" ELSE "'. __('message.in_active') .'" END)'), 'LIKE', $search . '%');
				//$query->orWhereRaw("IF(template = 1 ,'Dynamic Page','No') LIKE '%$search%'");
				//$query->orWhereRaw($this->datatable_isactive("is_active"). "LIKE '%$search%'");
			}else{
				$query->where('title_mr','LIKE',"%$search%"); 
			}
			//$query->orWhereRaw("IF(sub_menu = 1 ,'Yes','No') LIKE '%$search%'");
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
            //$payload['slug_mr'] = hindi_slug($payload['title_mr']);
            $payload['slug_mr'] = MenuHelperService::generateHindiSlug($payload['title_mr']);
        }
		
		if (!empty($payload['show_in'])) {
            $payload['show_in'] = implode(',', $payload['show_in']);
        } 
		
		if(!empty($payload['parent_id']) && $payload['parent_id'] !=''){
             $payload['parent_id'] = $payload['parent_id'];   
		}
		else{
			$payload['parent_id'] =NULL;
		}
		
		if(!empty($id)){ 
             $payload['updated_by'] =AuthId();   
		}
		else{
			$payload['created_by'] =AuthId();
		}
		
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