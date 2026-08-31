<?php 
declare (strict_types = 1);

namespace App\Web\Slider;

use App\Core\BaseService;
use App\Web\Slider\Slider as Model;
use App\Web\Slider\SliderResource as Resource;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use DB;

class SliderService extends BaseService 
{
    protected static $model = Model::class;
    protected static $resource  = Resource::class;

    protected $columns = [
	    2 => 'type',
        3 => 'title_en',
		4 => 'title_mr',
		5 => 'sort_order',
		6 => 'url',
        7 => 'is_active'
    ];

    public function getDataTableList()
    {
        [$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query =Model::select('cms_sliders.*');

        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
			if ($this->english_characters($search)) {
				$type=1;
				$query->where('title_en','LIKE',"%$search%")
				->orWhere('sort_order','LIKE',"%$search%")
				->orWhere('url','LIKE',"%$search%")
				->orWhere(DB::raw("(CASE WHEN cms_sliders.type=".$type.' THEN "Top Slider" ELSE "Bottom Slider" END)'), 'LIKE', $search . '%')
				->orWhere(DB::raw("(CASE WHEN cms_sliders.is_active=". config('constant.ACTIVE') .' THEN "'. __('message.active') .'" ELSE "'. __('message.in_active') .'" END)'), 'LIKE', $search . '%');
			}else{
				$query->orWhere('title_mr','LIKE',"%$search%"); 
			}
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
            $payload['slug_mr'] = SliderHelperService::hindi_slug($payload['title_mr']);
        }

		$payload['url'] = $payload['url'];
		
        if(!empty($payload['images'])){
			$payload['images'] = $payload['images'];
		}
		 
		if ($id) {
	        $payload['updated_by'] = AuthId();
	        $slider = Model::findOrFail($id);
	        $slider->update($payload);
	        return $slider;
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