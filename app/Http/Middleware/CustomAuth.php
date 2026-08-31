<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB; 
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Api\V1\Auth\AuthService;
use App\Web\StatePermissions\StatePermissionsController;

class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
	 
	
	public function __construct(private AuthService $service, private StatePermissionsController $statePermissionsController)
     {
        $this->service = $service;
		
     }
	 
    public function handle(Request $request, Closure $next): Response
    {
        $reqData = $request->all();
		$pathInfo = $request->getPathInfo();
		//dd($request);
		$referer = $request->header('referer');
		$refererArray = explode("/",$referer);       
		$pathArray = explode("/",$pathInfo); 
		//dd($pathArray);
		if($pathArray[1]=='redirect-from-state-portal' && ($pathArray[2]=='fail' || $pathArray[2]=='success') )
		{
			if(isset($pathArray[3]) && $pathArray[3]!='' && $request->isMethod('get')){
				$query = DB::table('state_permission_api_log')
				->select( 'api_request','state_id','state_name','sent_by');

				$query->where('unque_ref_id',$pathArray[3]);
				$logData = $query->get()->first();
				//dd($logData);
				if($logData!=''){
					$userData = DB::table('users')
					->select('email')
						->where('id', $logData->sent_by)->get()
						->first();
					
				}
			}else if($pathArray[1]=='redirect-from-state-portal' && $request->isMethod('post')){
				$requestedData = $request->all();
				
				$requestedData['requestedParams'] = json_decode($requestedData['requestedParams'],true);
				$validation = $this->validateClientResponse($requestedData['requestedParams']);
				//dd($requestedData['requestedParams']);
				if($validation === true){
					
				$refererId = $requestedData['requestedParams']['params']['unque_ref_id'];
				$query = DB::table('state_permission_api_log')
				->select( 'api_request','state_id','state_name','sent_by');

				$query->where('unque_ref_id',$refererId);
				$logData = $query->get()->first();
				//dd($logData);
					if($logData!=''){
						$userData = DB::table('users')
						->select('email')
							->where('id', $logData->sent_by)->get()
							->first();
						
					}
				}else{
					
					return response()->json($validation, 400);
				}
							
			}			
			//dd($userData);
			$api_request = json_decode($logData->api_request);
			if($userData->email!=''){				
				$key = $userData->email;
				$user = (new LoginRequest())->getAuthUser($key);
				$permissions = $this->service->getUserPermissionsAssigned($user->id);
				Auth::login($user, false);
				session(['permissions' => $permissions]);			
				session([
					'project_type' => $api_request->params->application_type
				]);			
			}
		}		
		return $next($request);	
    }
	public function validateClientResponse($requestedParams){
		
		$error='';
		$message='';
		/*if(!isset($requestedParams['auth']['token'])){
			return ['error' =>400,'message' => "Auth Token is not available"];
		}*/		
		if(!isset($requestedParams['userDetails']['email']) || $requestedParams['userDetails']['email']==''){
			return ['error' =>400,'message' => "User Details not available"];
		}
		if(!isset($requestedParams['userDetails']['mobile']) || $requestedParams['userDetails']['mobile']==''){
			return ['error' =>400,'message' => "User Details not available"];
		}
		if(!isset($requestedParams['params']['unque_ref_id']) || $requestedParams['params']['unque_ref_id']==''){
			return ['error' =>400,'message' => "Reference ID is not available"];
		}
		/*if(!isset($requestedParams['params']['api_checksum']) || $requestedParams['params']['api_checksum']==''){
			return ['error' =>400,'message' => "Checksum is not available"];
		}*/
		if(!isset($requestedParams['params']['application_type']) || $requestedParams['params']['application_type']==''){
			return ['error' =>400,'message' => "Application Type is not available"];
		}
		/*if(!isset($requestedParams['params']['state_ref_id']) || $requestedParams['params']['state_ref_id']==''){
			return ['error' =>400,'message' => "State reference id is not available"];
		}*/
		if($requestedParams['params']['application_type']!=1 && $requestedParams['params']['application_type'] !=2){
			return ['error' =>400,'message' => "Application Type is not Valid"];
		}
		if(!$this->validateUniqueRefId($requestedParams['params']['unque_ref_id'])){
			return ['error' =>400,'message' => "Unique Reference ID mismatched"];
		}
		/*
		$confData = $this->getSecretKey($requestedParams['params']['unque_ref_id']);
		
		if(!$this->validateToken($confData->state_name,$requestedParams['auth']['token'],$confData->secret_key)){
			return ['error' =>400,'message' => "Token is invalid"];
		}
		if(!$this->validateChecksum($requestedParams['userDetails']['email'],$requestedParams['userDetails']['mobile'],$requestedParams['params']['api_checksum'],$confData->secret_key)){
			return ['error' =>400,'message' => "Checksum is invalid"];
		}*/
		return true;
	}
	public function getSecretKey($unque_ref_id){
		
		$query = DB::table('state_permission_api_log as log')
				->select( 'log.state_id','log.state_name','log.sent_by')
				->join('states as st','st.id','=','log.state_id');
				$query->where('log.unque_ref_id',$unque_ref_id);
				
		$confData = $query->get()->first();
		return $confData;
	}
	public function validateToken($state_name,$token,$secret_key){
		$decryptedToken = $this->statePermissionsController->decrypt($state_name,$token);
		if($decryptedToken === $secret_key){
			return true;
		}else{
			return false;
		}
	}
	public function validateChecksum($email,$mobile,$api_checksum,$secret_key){
		$generatedChecksum = hash('sha256', $email.$mobile.$secret_key); 
		if($generatedChecksum === $api_checksum){
			return true;
		}else{
			return false;
		}
	}
	public function validateUniqueRefId($unque_ref_id){
		$query = DB::table('state_permission_api_log')
				->select( 'state_id','state_name','sent_by');

		$query->where('unque_ref_id',$unque_ref_id);
		$logData = $query->get()->toArray();
		if(count($logData)>0){
			return true;
		}else{
			return false;
		}
	}
	public function createAccessToken(User $user)
    {
        return $user->createToken(['user_id' => $user->id])->accessToken;
    }
}
