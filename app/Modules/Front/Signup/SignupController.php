<?php 

declare(strict_types=1);

namespace App\Modules\Front\Signup;

use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Front\Signup\SignupService;
use App\Http\Api\V1\Front\Signup\SignupValidation as Validation;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Session;

final class SignupController extends ClientController
{
    
    public function __construct(private SignupService $service)
    {
        $this->service = $service;
    }

    public function index(): View
    {	
		//Session::forget('step1');	
		$step1=Session::get('step1'); 
		//print_r($step1);exit;
        return view('front.signup')->with('title','Signup | Step1 Basic Details')->with('current_step',1)->with('details', (array) $this->service->getStep1Details());
    }
	
	public function store(Request $request): mixed 
    {   
        try 
        { 
			$validator = Validation::getStep1Rules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
			$is_Mobile_Verified=Session::get('mobile'); 
            $is_Amobile_Verified=Session::get('alternate_mobile');
            $is_email_Verified=Session::get('email');
            $is_Aemail_Verified=Session::get('alternate_email');
            
            if($is_Mobile_Verified==null || $is_Amobile_Verified==null || $is_email_Verified==null || $is_Aemail_Verified==null)
             {  
                 return $this->error([],__('message.vefircation_fields_error'));
             }
			
			session(['step1' =>$validator->validated()]);
			
			return $this->success([],'Step1 Basic Details Successfully!');
               
            //$response = $this->service->create($validator->validated()); 
            //return $this->created($response);
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    } 


    public function mine_vendor_block_details()
    {   
	    $step1=Session::get('step1');
		if(empty($step1)){
			return redirect('signup');
		} 
        return view('front.mine_vendor_block_details')->with('title','Signup | Step2 Block Details')->with('current_step',2)->with('details', (object) $this->service->getStep2Details());
    }
	
	
	public function post_mine_vendor_block_details(Request $request) 
    {
		try 
        { 
			$validator = Validation::getStep2Rules($request->all());
            
            if ($validator->fails()) 
            {
                return $this->error($validator->errors());
            }
			
			/*$is_Mobile_Verified=Session::get('mobile'); 
            $is_Amobile_Verified=Session::get('alternate_mobile');
            $is_email_Verified=Session::get('email');
            $is_Aemail_Verified=Session::get('alternate_email');
            
            if($is_Mobile_Verified==null || $is_Amobile_Verified==null || $is_email_Verified==null || $is_Aemail_Verified==null)
             {  
                 return $this->error([],__('message.vefircation_fields_error'));
             }*/
			
            $step1=Session::get('step1');   
            //$response = $this->service->create($validator->validated(),$step1);
			$response = $this->service->create($request->all(),$step1);			
            Session::forget('step1');
            return $this->success([],'Registration completed seccessfully.Your password generate after approve then password send on mobile and email');
        }
        catch (\Throwable $e) 
        {
            return $this->handleException($e);
        }
    }	


    

}