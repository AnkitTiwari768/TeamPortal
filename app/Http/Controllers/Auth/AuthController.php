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

class AuthController extends Controller
{
    public function __construct(AuditTrailLog $auditTrailLog, private AuthService $service)
    {
        $this->auditTrailLog = $auditTrailLog;
        $this->service = $service;
    }

    public function index()
    {

        crypto_secrets();
        return view('auth.login-new')
            ->with('crypto_salt', session('crypto_salt'))
            ->with('crypto_iv', session('crypto_iv'))
            ->with('crypto_key', session('crypto_key'))
            ->with('crypto_key_size', session('crypto_key_size'))
            ->with('crypto_iterations', session('crypto_iterations'))
            ->with('title', __('message.login'));
    }

    public function store(LoginRequest $request)
    {
        $result = $request->authenticate();
        $request->session()->regenerate();

        if ($result) {
            $user = (new LoginRequest())->getAuthUser($request?->username);

            // Retrieve all user roles
            $userRoleSlugs = \DB::table('user_roles')
                ->join('roles', 'user_roles.role_id', '=', 'roles.id')
                ->where('user_roles.user_id', $user->id)
                ->pluck('roles.slug')
                ->toArray();

            // Resolve priority role: snp > lsp > bnp > first available
            $defaultRoleSlug = $this->service->resolveDefaultRole($userRoleSlugs);

            $permissions = $this->service->getUserPermissionsAssigned(
                $user->id,
                $defaultRoleSlug ?: null
            );

            Auth::login($user, $request->boolean('remember'));

            if (! auth()->user()->status) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return response()->json([
                    'status' => false,
                    'message' => '',
                    'errors' =>  'Your account is blocked. Please contact to site administrator',
                ], 200);
            }
            $data = $request->all();
            unset($data['_token']);
            unset($data['password']);
            unset($data['captcha']);
            $this->auditTrailLog
                ->setModuleName('Login')
                ->setActivityType('User Logged In')
                ->setActivityData((array) $data)
                ->save();

            // Store the priority-role-scoped permissions and pre-select that role.
            session([
                'permissions' => $permissions,
                'active_role' => $defaultRoleSlug ?: null,
            ]);
            return response()->json([
                'status' => true,
                'url' => url('dashboard'),
                'message' => 'Login successfully'
            ], 200);
        }
    }

    public function destroy(Request $request)
    {
        if ($user = $request->user()) {
            $this->auditTrailLog
                ->setModuleName('Logout')
                ->setActivityType('User Logged Out')
                ->setActivityData([
                    'username' => $user->username,
                    'message' => $user->username . ' logged out'
                ])
                ->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('status', 'You have been logged out successfully.');
    }


    public function myCaptcha()
    {
        return view('myCaptcha');
    }

    public function myCaptchaPost(Request $request)
    {
        request()->validate(
            [
                'email' => 'required|email',
                'password' => 'required',
                'captcha' => 'required|captcha'
            ],
            ['captcha.captcha' => 'Invalid captcha code.']
        );
        dd("You are here :) .");
    }


    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function refreshCaptcha()
    {
        return response()->json(['captcha' => captcha_img()]);
    }
}
