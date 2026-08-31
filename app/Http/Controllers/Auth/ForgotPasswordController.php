<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\ClientController;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\Models\User;
use Mail;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Password;
use Validator;
use Session;
use URL;
use Illuminate\Validation\ValidationException;
use App\Http\Api\V1\Auth\AuthService;
use App\Http\Services\VerificationService;
use Redirect;
use App\Services\PHPMailerService;

class ForgotPasswordController extends ClientController
{
    public function __construct(private AuthService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('auth.forgetPassword')->with('title', __('message.forgot_password'));
    }

    public function forgetUdhayamPage()
    {
        return view('auth.forgetUdhayam')->with('title', __('message.forgot_udhayam_number'));
    }

    public function getAuthUser($type, $username)
    {
        if ($type == 'email') {
            return User::where('email', $username)
                ->orWhere('alternate_email', $username)
                ->first();
        } else {
            return User::where('mobile', $username)
                ->orWhere('alternate_mobile', $username)
                ->first();
        }
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'captcha' => 'required|captcha',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ]);
        }
        $user = $this->getAuthUser('email', $request->email);
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => "This credentials does not match our record"
            ]);
            //return back()->with('error', __('auth.failed'));  
        }


        $token = Str::random(64);

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);


        $templateData = [
            'token' => $token,
        ];
        $body = view('emails.forgetPassword', $templateData)->render();
        $to = $request->email;
        $subject = "Reset Password";

        app(PHPMailerService::class)->sendEmail($to, $subject, $body);

        /*Mail::send('emails.forgetPassword', ['token' => $token], function($message) use($request){
			$message->to($request->email);
			$message->subject('Reset Password');
		});*/
        return response()->json([
            'status' => true,
            'message' => 'We have e-mailed your password reset link!.'
        ]);

        //return view('emails.forgetPassword',compact('token','token'));
    }

    public function resetPassword($token)
    {
        return view('auth.resetPassword', ['token' => $token, 'title' => 'Reset Password'])
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }


    public function storeResetPassword(Request $request)
    {
        try {
            $request->merge([
                'current_password' => crypto_decrypt($request->current_password),
                'password' => crypto_decrypt($request->password),
                'password_confirmation' => crypto_decrypt($request->confirmpassword),
            ]);

            /*$request->validate([
                'email' => 'required|email|exists:users',
                'password' => ['required', 'confirmed', Rules\Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
                'password_confirmation' => 'required',			
                'captcha' => 'required|captcha',
            ]);*/


            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:users',
                'password' => ['required', 'confirmed', Rules\Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()],
                'password_confirmation' => 'required',
                'captcha' => 'required|captcha',
            ]);

            if ($validator->fails())
                return $this->error($validator->errors());


            $updatePassword = DB::table('password_resets')
                ->where([
                    'email' => $request->email,
                    'token' => $request->token
                ])
                ->first();

            if (!$updatePassword) {
                //return back()->withInput()->with('error', 'Invalid token!');
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid token.',
                    'errors' => ["Invalid token!"]
                ]);
            }

            $user = User::where('email', $request->email)
                ->orWhere('alternate_email', $request->email)
                ->update(['password' => Hash::make($request->password)]);

            $userData = User::where('email', $request->email)->first();

            DB::table('password_resets')->where(['email' => $request->email])->delete();

            app(\App\Domain\EmailTemplate\EmailTemplateService::class)->send(
                templateKey: 'forget-password',
                toEmail: $userData->email,
                data: [
                    'user_id' => $userData->username,
                    'user_name' => $userData->first_name,
                    'password' => $request->password,
                    'current_year' => (string) date('Y')
                ]
            );


            return response()->json([
                'status' => true,
                'message' => 'Your password has been changed!.'
            ]);

            //return redirect('/login')->with('status', 'Your password has been changed!');
        } catch (Throwable $exception) {
            return $this->handler($error);
        }
    }

    //Mobile Reset Password

    public function reset_password_mobile(Request $request)
    {


        $optionType = $request->post('option_type');
        if ($optionType ==  'mobile') {
            $validator = Validator::make($request->all(), [
                'mobile' => 'required',
                'captcha' => 'required|captcha'
            ]);
            if ($validator->fails()) {
                return  response()->json(['status' => false, 'message' => 'Please enter valid mobile or captcha']);
            }
        }

        $user = $this->getAuthUser('mobile', $request->mobile);
        if (!$user) {
            return  response()->json(['status' => false, 'message' => __('auth.failed')]);
        }

        Session::put('mobile', $request['mobile']);


        $this->service->sendVerificationCode($request?->mobile, true);
        $this->setVerifyOtpSession($request?->mobile);

        return response()->json(['status' => true, 'url' => URL::to('/resetpassword'), 'message' => 'OTP has been sent']);
    }

    public function resetPasswordByMobile(Request $request)
    {
        return view('auth.reset-password-mobile', ['title' => 'Reset Password'])
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'));
    }

    private function setVerifyOtpSession($username)
    {
        session([
            'username' => $username,
            'enableVerifyOtpPageSession' => true,
            'pageExipred' => date('Y-m-d H:i:s', (time() + VerificationService::CODE_EXPIRATION_TIME))
        ]);
    }

    public function verify_otp(Request $request)
    {

        $request->merge([
            'password' => $request->get('password') ? crypto_decrypt($request->get('password')) : $request->get('password'),
            'password_confirmation' => $request->get('password_confirmation') ? crypto_decrypt($request->get('password_confirmation')) : $request->get('password_confirmation'),
        ]);

        $request->validate([
            'userotp' => ['required', 'numeric', 'digits:6'],
            'password' => ['required', 'confirmed', Rules\Password::min(8)
                ->mixedCase()
                ->numbers()
                ->symbols()
                ->uncompromised()],
            'captcha' => 'required|captcha'
        ]);
        $mobile = Session::get('mobile');
        $key = $mobile;    //$request['username'];
        $code = (int) $request['userotp'];

        $result = VerificationService::verify($key, $code);


        if (
            $result === VerificationService::CODE_INVALID ||
            $result === VerificationService::CODE_EXPIRED
        ) {
            //return back()->with('message', 'Entered OTP is incorrect or expired!'); 
            return response()->json([
                'status' => false,
                'message' => 'Entered OTP is incorrect or expired!'
            ]);
        }

        DB::table('users')
            ->where('mobile', $mobile)
            ->orWhere('alternate_mobile', $mobile)
            ->update(['password' => Hash::make($request->password)]);

        //For sending SMS
        // $password = crypto_decrypt($request->get('password'));
        // $tdetails=forgotPasswordSmsTemplate();
        // $message=str_replace("{#var#}",$password,$tdetails['msg']);
        // sendSingleUnicode($message,$mobile,$tdetails);

        Session::forget('mobile');
        return true;
        // return Redirect::back()->with('status', 'Your password has been changed!');
        //return redirect('/login')->with('status', 'Your password has been changed!');
    }

    public function resendOtp(Request $request)
    {

        $this->service->sendVerificationCode($request?->mobile, true);
        $this->setVerifyOtpSession($request?->mobile);
        return response()->json([
            'status' => true
        ]);
    }
}
