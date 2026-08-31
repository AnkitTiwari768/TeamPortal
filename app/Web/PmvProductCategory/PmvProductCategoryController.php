<?php 

declare(strict_types=1);

namespace App\Web\PmvProductCategory;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;

class PmvProductCategoryController extends ClientController
{
    
    public function __construct(private PmvProductCategoryService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {      
        $title = 'Pmv Product Category List';        
        return view('pmv_product_category.index', compact('title'));  
    }

    public function getCategories()
    {      
        return $this->success($this->service->getCategories());
    }

    public function create(): View
    {      
        $getAllSubDomainName = $this->service->getAllSubDomainName();
        $title = 'Add Pmv Product Category';
        $module_url = 'pmv-category-list';
        return view('pmv_product_category.form',compact('title','module_url','getAllSubDomainName'));
    }

    public function pmvCreateCategory(Request $request)
    {   
        $validator = Validator::make(
            $request->all(),
            PmvProductCategoryRequest::getRules(),
             PmvProductCategoryRequest::messages()
        );
        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }

        $this->service->storeCategory(
            $validator->validated()
        );

        return response()->json([
            'status' => true,
            'message' => 'PMV Category Created Successfully'
        ]);
    }

    public function edit(string $id): View
    {      
        $title = 'Edit Pmv Product Category';
        $module_url = 'pmv-category-list';     
        $row = $this->service->getCategory($id);
        $getAllSubDomainName = $this->service->getAllSubDomainName();
        return view('pmv_product_category.form',compact('title','module_url','row','id','getAllSubDomainName'));
    }

    public function pmvUpdateCategory(Request $request,string $id)
    {   
        $validator = Validator::make(
            $request->all(),
            PmvProductCategoryRequest::getRules($id)
        );        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }       
        $this->service->storeCategory($validator->validated(),$id);
        return response()->json([
            'status' => true,
            'message' => 'PMV Category Updated Successfully'
        ]);
    }

}