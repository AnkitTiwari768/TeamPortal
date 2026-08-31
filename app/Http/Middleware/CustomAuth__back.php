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


class CustomAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
	 
	
	public function __construct(private AuthService $service)
     {
        $this->service = $service;
		
     }
	 
    public function handle(Request $request, Closure $next): Response
    {
        $reqData = $request->all();
		//if($request->isMethod('get') && )
		$pathInfo = $request->getPathInfo();
		//dd($request);
		$referer = $request->header('referer');
		$refererArray = explode("/",$referer);       
		$pathArray = explode("/",$pathInfo); 
		
		if($pathArray[1]=='redirect-from-state-portal' && ($pathArray[2]=='fail' || $pathArray[2]=='success') )
		{
			if(isset($pathArray[3]) && $pathArray[3]!='' && $request->isMethod('get')){
				$query = DB::table('state_permission_api_log')
				->select( 'state_id','state_name','sent_by');

				$query->where('unque_ref_id',$pathArray[3]);
				$logData = $query->get()->first();
				//dd($logData);
				if($logData!=''){
					$userData = DB::table('users')
					->select('email')
						->where('id', $logData->sent_by)->get()
						->first();
					
				}
			}else if(!isset($pathArray[3])  && $request->isMethod('post')){
				$requestedData = $request->all();
				$requestedData['requestedParams'] = json_decode($requestedData['requestedParams'],true);
				//echo gettype($requestedData['requestedParams']);
				$refererId = $requestedData['requestedParams']['params']['unque_ref_id'];
				$query = DB::table('state_permission_api_log')
				->select( 'state_id','state_name','sent_by');

				$query->where('unque_ref_id',$refererId);
				$logData = $query->get()->first();
				//dd($logData);
				if($logData!=''){
					$userData = DB::table('users')
					->select('email')
						->where('id', $logData->sent_by)->get()
						->first();
					
				}
				
			}
			
			//dd($userData);
			if($userData->email!=''){				
				$key = $userData->email;
				$user = (new LoginRequest())->getAuthUser($key);
				$permissions = $this->service->getUserPermissionsAssigned($user->id);
				Auth::login($user, false);
				session(['permissions' => $permissions]);			
				session([
					'project_type' => 1
				]);			
			}
		}
		
		return $next($request);	
    }
	public function createAccessToken(User $user)
    {
        return $user->createToken(['user_id' => $user->id])->accessToken;
    }
}
