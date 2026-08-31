<?php 

declare(strict_types=1);

namespace App\Web\Masters\Attributes;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Web\Masters\Attributes\AttributesService;
use App\Web\Masters\Attributes\AttributesRequest;

class AttributesController extends ClientController
{
    
	public function __construct(private AttributesService $service)
    {
        $this->service = $service;
    }
	
    public function getAttributes(Request $request)
    {

        $data = $this->service->getAttributesList($lang_type = null);
        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function index(): View
    {      
        $title = 'Attributes List';        
        return view('masters.attributes.index', compact('title'));  
    }

    public function getDataAttributes()
    {      
        return $this->success($this->service->getDataAttributes());
    }

    public function create(): View
    {      
        $title = 'Add Attributes';
        $module_url = 'Attributes-list';
        return view('masters.attributes.form',compact('title','module_url'));
    }
     public function attributesCreate(Request $request)
    {   
        $validator = Validator::make($request->all(),AttributesRequest::getRules(),AttributesRequest::messages());        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }

        $this->service->storeAttributes($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Attributes Created Successfully'
        ]);
    }
    public function edit($id): View
    {      
        $row = $this->service->getAttributesById($id);
        $title = 'Edit Attributes';
        $module_url = 'Attributes-list';
        return view('masters.attributes.form',compact('title','module_url','row','id'));
    }
     public function attributesUpdate(Request $request,string $id)
    {   
        $validator = Validator::make($request->all(),AttributesRequest::getRules($id));        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }       
        $this->service->storeAttributes($validator->validated(),$id);
        return response()->json([
            'status' => true,
            'message' => 'Attributes Updated Successfully'
        ]);
    }



   
   

}