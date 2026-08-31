<?php 

declare(strict_types=1);

namespace App\Web\AovCategory;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Web\AovCategory\{AovCategoryService,AovCategoryRequest,AovCategory};

class AovCategoryController extends ClientController
{
    
	public function __construct(private AovCategoryService $service)
    {
        $this->service = $service;
    }

	public function index(): View
    {      
        $title = __('Aov Category List');        
        return view('aov-categories.index', compact('title'));  
    }

    public function getCategories()
    {      
        return $this->success($this->service->getCategories());
    }
    

    public function create(): View
    {       
        $title = __('Add Category');
        $module_url = 'aov-categories-index';
        $ondcDomains = $this->service->getOndcDomains();
        return view('aov-categories.form', compact('title', 'module_url','ondcDomains'));
    }

    public function createCategory(Request $request) 
    {   
        $validator = Validator::make($request->all(), AovCategoryRequest::getRules());
        
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
        $title = __('Edit Category');
        $module_url = 'aov-categories-index';        
        $row = $this->service->getCategory($id);
        $ondcDomains = $this->service->getOndcDomains();
        return view('aov-categories.form', compact('title', 'module_url', 'row', 'id', 'ondcDomains'));
            
    }

    public function updateCategory(Request $request, string $id) 
    {   
        $validator = Validator::make($request->all(), AovCategoryRequest::getRules($id));
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
       
        return $this->updated(
            $this->service->storeCategory($validator->validated(), $id)
        );
    }
    


    public function view(string $id): View
    {      
        $title = __('view Category');
        $module_url = 'aov-categories-index';        
        $data = $this->service->getCategory($id);
        return view('aov-categories.show', compact('title', 'module_url', 'data'));
            
    }

    public function deleteCategory($id)
    {
        //dd($id);
        $category = AovCategory::find($id);

        if (!$category) {
            return response()->json([
                'status' => false,
                'message' => 'Category not found.'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully.'
        ], 200);
    }


    public function getOndcDomainType(Request $request)
    {
        $domain_type = $request->domain_type ?? [];

        $data = $this->service->getOndcDomainType($domain_type);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }

    public function getPublicProductCategory(string $id = null)
    {
        $data = AovCategory::select('id','name','aov_grouping_type')
        ->where('status', 1)
        ->get();

        return response()->json($data);
    }
    
}