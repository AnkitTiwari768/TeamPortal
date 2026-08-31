<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rules;
use Session;
use Illuminate\Support\Str;
use DB;
use App\Observers\AuditTrailLog;
use App\Http\Services\VerificationService;
use App\Http\Api\V1\Auth\AuthValidation as Validator;
use App\Http\Api\V1\Auth\AuthService;
use App\Http\Api\V1\Country\CountryService;

class AuthNewController extends Controller 
{
    public function __construct(AuditTrailLog $auditTrailLog, private AuthService $service)
     {
        $this->auditTrailLog = $auditTrailLog;
        $this->service = $service;
     }
	
    public function index()
    {
	
        crypto_secrets();
        return view('auth.login')
        ->with('crypto_salt', session('crypto_salt'))
        ->with('crypto_iv', session('crypto_iv'))
        ->with('crypto_key', session('crypto_key'))
        ->with('crypto_key_size', session('crypto_key_size'))
        ->with('crypto_iterations', session('crypto_iterations'))
		->with('title', __('message.login'))        
        ->with('isdCodes', (new CountryService())->getIsdCodes());
    }

    // public function index()
    // {        
    //     return view('auth.login-new')
	// 	->with('title', __('message.login'))
    //     ->with('isdCodes', (new CountryService())->getIsdCodes());
    // }

    private function setVerifyOtpSession($username)
    {
        session([
            'username' => $username,
            'enableVerifyOtpPageSession' => true,
            'pageExipred' => date('Y-m-d H:i:s', (time() + VerificationService::CODE_EXPIRATION_TIME))
        ]);
    }
	
	
	public function store(LoginRequest $request)
    { 
        $result = $request->authenticate();
        $request->session()->regenerate();
        
        if ($result)
        {
            $user = (new LoginRequest())->getAuthUser($request?->username);
            $this->service->sendVerificationCode($request?->username, true, $user);
            $this->setVerifyOtpSession($request?->username);
            //return redirect()->route('verify-otp');
            return response()->json([  
                'status' => true,
                'csrf_token' => csrf_token(),
                'message'=> 'OTP sent successfully'
            ], 200);
        }
    }

    public function resendOtp(Request $request)
    {
        $user = (new LoginRequest())->getAuthUser($request?->username);
        $this->service->sendVerificationCode($request?->username, true, $user);
        $this->setVerifyOtpSession($request?->username);
        return response()->json([
            'status' => true
        ]);
    }
	
	public function destroy(Request $request)
    {    
        $this->auditTrailLog
            ->setModuleName('Logout')
            ->setActivityType('User Logged Out')
            ->setActivityData((array) [auth()->user()->username.' Logged out'])
            ->save(); 

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
	
	public function myCaptcha()
    {
        return view('myCaptcha');
    }

    public function myCaptchaPost(Request $request)
    {
        request()->validate([
            'email' => 'required|email',
            'password' => 'required',
            'captcha' => 'required|captcha'
        ],
        ['captcha.captcha'=>'Invalid captcha code.']);
        dd("You are here :) .");
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function refreshCaptcha()
    {
        return response()->json(['captcha'=> captcha_img()]);
    }

    public function verifyOtpPage()
    {
        if (!Session::get('enableVerifyOtpPageSession')) 
        {
            abort(404);
        }

        // if (strtotime(Session::get('pageExipred')) < strtotime(currentDateTime()))
        // {
        //     abort(404);
        // }

        crypto_secrets();
        return view('auth.verify-otp-new')
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'))
            ->with('title', __('message.verify_otp'));
    }

    public function verifyOTP(Request $request)
    {

        $key = $request?->username;
        $code = (int) crypto_decrypt($request->otp);

        $validator = config('settings.enable_captcha') 
            ? Validator::getVerifyOtpWithCaptchaValidator($request->all())
            : Validator::getVerifyOtpValidator($request->all());
        
        if ($validator->fails()) 
        {
            return response()->json([  
                'status' => false,
                'message'=>'Validation Error',
                'errors' =>  $validator->errors(), 
            ], 200);
           // return redirect('verify-otp')->with('errors', $validator->errors());
        }

        $result = VerificationService::verify($key, $code);
        
        if (
            $result === VerificationService::CODE_INVALID || 
            $result === VerificationService::CODE_EXPIRED
        )
        {
           return response()->json([  
                'status' => false,
                'message'=>'',
                'errors' =>  'Entered OTP is incorrect or expired!', 
            ], 200);
            //return redirect('verify-otp')->with('flash_error', 'Entered OTP is incorrect or expired!');
        }
        
        if ($result === VerificationService::CODE_VERIFIED)
        { 
            $user = (new LoginRequest())->getAuthUser($key);

            // Determine the first assigned role and scope permissions to that role.
            $firstRoleSlug = \DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $user->id)
                ->value('roles.slug');

            $permissions = $this->service->getUserPermissionsAssigned(
                $user->id,
                $firstRoleSlug ?: null
            );
            
            Auth::login($user, $request->boolean('remember'));

            if (! auth()->user()->status) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->json([  
                    'status' => false,
                    'message'=>'',
                    'errors' =>  'Your account is blocked. Please contact to site administrator', 
                ], 200);
            }
            $this->auditTrailLog
            ->setModuleName('Login')
            ->setActivityType('User Logged In')
            ->setActivityData((array) $request->all())
            ->save(); 

             $applicantQuery = DB::table('applicants')
            ->select('production_type')
            ->where('user_id', $user->id)
            ->first();
            if($applicantQuery){
                session(['project_type' => $applicantQuery->production_type]);
            }

            // Store the first-role-scoped permissions and pre-select that role.
            session([
                'permissions' => $permissions,
                'active_role' => $firstRoleSlug ?: null,
            ]);
            return response()->json([  
                'status' => true,
                'url' => url('dashboard'),
                'message'=> 'OTP verified successfully'
            ], 200);
        }
        
       // return redirect('verify-otp')->with('flash_error', 'Entered OTP is incorrect or expired!');
        
    }

    public function createAuthSession($userId) 
    {
        $user = User::find($userId);
        $permissions = $this->service->getUserPermissionsAssigned($user->id);
        Auth::login($user, false);
        
        if (! auth()->user()->status) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            throw ValidationException::withMessages([
                'email' => trans('Your account is blocked. Please contact to site administrator'),
            ]);
        }

        session(['permissions' => $permissions]);
        return redirect('dashboard');
    }
}