<?php 

declare(strict_types=1);

namespace App\Web\Language;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Core\BaseRequest;
use App\Web\Language\LanguageService;
use App\Web\Language\LanguageRequest;

class LanguageController extends ClientController
{
    
	public function __construct(private LanguageService $service)
    {
        $this->service = $service;
    }
	
    public function getlanguage(Request $request)
    {
        $data = $this->service->getLanguageList($lang_type = null);

        return response()->json([
            'status' => true,
            'data'   => $data
        ]);
    }


    public function index(): View
    {      
        $title = 'Language List';        
        return view('language.index', compact('title'));  
    }

    public function getDataLanguage()
    {      
        return $this->success($this->service->getDataLanguage());
    }

    public function create(): View
    {      
        $title = 'Add Language';
        $module_url = 'language-list';
        return view('language.form',compact('title','module_url'));
    }
     public function languageCreate(Request $request)
    {   
        $validator = Validator::make($request->all(),LanguageRequest::getRules(),LanguageRequest::messages());        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }

        $this->service->storeLanguage($validator->validated());

        return response()->json([
            'status' => true,
            'message' => 'Language Created Successfully'
        ]);
    }
    public function edit($id): View
    {      
        $row = $this->service->getLanguageById($id);
        $title = 'Edit Language';
        $module_url = 'language-list';
        return view('language.form',compact('title','module_url','row','id'));
    }
     public function languageUpdate(Request $request,string $id)
    {   
        $validator = Validator::make($request->all(),LanguageRequest::getRules($id));        
        if ($validator->fails()) 
        {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ],422);
        }       
        $this->service->storeLanguage($validator->validated(),$id);
        return response()->json([
            'status' => true,
            'message' => 'Language Updated Successfully'
        ]);
    }



   
   

}