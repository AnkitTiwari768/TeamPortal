<?php 
declare(strict_types=1);
namespace App\Web\Category;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController; 
use App\Web\Category\{CategoryServic};


class CategoryController extends ClientController
{
    private static string $module = 'categories.index';

    public function __construct(private CategoryService $service){}

    public function index(): View
    {
        //guard(config('permissions.department-view'));        
        $title = __('Category List');        
        return view('categories.index', compact('title'));  
    }

    public function getCategories()
    {
        //guard(config('permissions.department-view'));
        
        return $this->success($this->service->getCategories());
    }
    

    public function create(): View
    {
        //guard(config('permissions.department-create'));        
        $title = __('Add Category');
        $module_url = static::$module;
        return view('categories.form', compact('title', 'module_url'));
    }

    public function createCategory(Request $request) 
    {   
        //guard(config('permissions.department-create'));
        $validator = Validator::make($request->all(), CategoryRequest::getRules());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
        return $this->created(
            $this->service->storeCategory($validator->validated())
        );
    }

    public function edit(string $id): View
    {
        //guard(config('permissions.department-update'));        
        $title = __('Edit Category');
        $module_url = static::$module;        
        $row = $this->service->getCategory($id);
        return view('categories.form', compact('title', 'module_url', 'row', 'id'));
            
    }

    public function updateCategory(Request $request, string $id) 
    {   
        //guard(config('permissions.department-update'));
        
        $validator = Validator::make($request->all(), CategoryRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeCategory($validator->validated(), $id)
        );
    }
}