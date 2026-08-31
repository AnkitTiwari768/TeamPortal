<?php declare(strict_types=1);

namespace App\Modules\Block;
use App\Http\Controllers\ClientController;
use App\Exceptions\ValidationException; 
use App\Http\Api\V1\Block\BlockService; 
use App\Http\Services\CommonService; 
use App\Http\Api\V1\Block\BlockValidation as Validation;
use Illuminate\Http\Request;
use Session;

final class BlockController extends ClientController 
{
	private static $module = 'blocks.index';
    protected $service;
    protected $commonService;

    public function __construct(BlockService $service,CommonService $commonService)
    {
        $this->service = $service;
        $this->commonService = $commonService;
    }

    public function index()
    {
       // guard('blocks-view');
        return view('block.index')
            ->with('title', __('message.blocks_list'));
    }

    public function datalist()
    {
        //guard('blocks-view');
        $response = $this->service->getDataTableList(); 
        return $this->success($response);
    }

    public function create()
    {
        //guard('blocks-create'); 
        return view('block.form')
            ->with('title', __('message.add_block'))
            ->with('module_url', self::$module)
            ->with('details', $this->service->getDetails());
             
    }

    public function store(Request $request) 
    {   
        //guard('blocks-create');
        try 
        {   
			$validator=Validation::getRules($request->all(),null);
            if ($validator->fails()) 
               return $this->error($validator->errors()); 

            $is_Mobile_Verified=Session::get('mobile'); 
            $is_Amobile_Verified=Session::get('alternate_mobile');
            $is_email_Verified=Session::get('email');
            $is_Aemail_Verified=Session::get('alternate_email');
            
            if($is_Mobile_Verified==null || $is_Amobile_Verified==null || $is_email_Verified==null || $is_Aemail_Verified==null)
            {  
                 return $this->error([],__('message.vefircation_fields_error'));
            }
           
 
            $file=$request->file('loi_bidder_file');
            $originalFileName = explode('.',$file->getClientOriginalName());   
            if(count($originalFileName) > 2) {
                return $this->error([],'Double extention is not allowed');
            }
             
            $response = $this->service->create($validator->validated());

                Session::forget('mobile'); 
                Session::forget('alternate_mobile'); 
                Session::forget('email'); 
                Session::forget('alternate_email');
                
            return $this->created($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }

    public function edit($id)
    {
        //guard('blocks-update'); 
        $row = $this->service->findById($id); 
       // dd($row);
        return view('block.form', compact('id', 'row'))
            ->with('title', __('message.edit_block'))
            ->with('module_url', self::$module)
            ->with('details', $this->service->getDetails());
    }

    public function show($id)
    {
        //guard('blocks-update');
        try 
        {
            $response = $this->service->findById($id);
            return $this->success($response);
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }


    public function update(Request $request, $id) 
    {
       // guard('blocks-update');
        try 
        {
            $ifExistResult=$this->checkIfExistData($request->all()); 
          // dd($ifExistResult);
            if($ifExistResult!=true){

                 return $this->error([],__('message.vefircation_fields_error'));
                
            }

                $is_Mobile_Verified=Session::get('mobile'); 
                $is_Amobile_Verified=Session::get('alternate_mobile');
                $is_email_Verified=Session::get('email');
                $is_Aemail_Verified=Session::get('alternate_email');
                
                if($is_Mobile_Verified==null || $is_Amobile_Verified==null || $is_email_Verified==null || $is_Aemail_Verified==null)
                {  
                     return $this->error([],__('message.vefircation_fields_error'));
                }
            

			$validator=Validation::getRules($request->all(),(string) $id);
            if ($validator->fails()) 
               return $this->error($validator->errors()); 

            $this->service->update($validator->validated(), $id);
            Session::forget('mobile'); 
            Session::forget('alternate_mobile'); 
            Session::forget('email'); 
            Session::forget('alternate_email');
            return $this->updated();
        }
        catch (Throwable $exception) 
        {
            return $this->handler($error);
        }
    }

    public function checkIfExistData($payload)
    {
        if($payload['mobile']){
           $result1=$this->commonService->getExistData('users','mobile',$payload['mobile']); 
           if($result1){
                Session::put('mobile', "1");
            }else{
                if(Session::get('mobile') != 1)
                    Session::put('mobile', "");
            }
          
        }
        if($payload['alternate_mobile']){
           $result2=$this->commonService->getExistData('users','alternate_mobile',$payload['alternate_mobile']); 
           if($result2){
            Session::put('alternate_mobile', "1");
            }else{
                if(Session::get('alternate_mobile') != 1)
                    Session::put('alternate_mobile', "");
            }
        }

        if($payload['email']){
            $result2=$this->commonService->getExistData('users','email',$payload['email']); 
            if($result2){
             Session::put('email', "1");
             }else{
                if(Session::get('email') != 1)
                    Session::put('email', "");
             }
         }

         if($payload['alternate_email']){
            $result2=$this->commonService->getExistData('users','alternate_email',$payload['alternate_email']); 
            if($result2){
             Session::put('alternate_email', "1");
             }else{
                if(Session::get('alternate_email') != 1)
                    Session::put('alternate_email', "");
             }
         }
  
    }
}