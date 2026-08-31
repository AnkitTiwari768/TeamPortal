<?php

namespace App\Domain\UbpIntegration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Api\V1\Auth\AuthService;
use Illuminate\Support\Facades\Cache;
use App\Domain\UbpIntegration\UbpService;
use App\Http\Controllers\ClientController;

class UbpLandingController extends ClientController
{
    public function __construct(private
        UbpTokenService $tokenService, private
        AuthService $authService,private
        UbpService $ubpservice
        )
    {
    }

    public function login(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(400, 'Token is missing.');
        }

        $payload = $this->tokenService->decryptUserToken($token);

        if (!$payload) {
            abort(401, 'Invalid or expired token.');
        }

        $udyamNo = $payload['udyam_no'] ?? null;
        $mobile  = $payload['mobile'] ?? null;
        //$email   = $payload['email'] ?? null;

        $msme = \DB::table('team_msme_schemes')
            ->where('udyam_no', $udyamNo)
            ->where('mobile', $mobile)
            ->first();

        if ($msme) {

            $user = User::where('mobile', $mobile)->where('id', $msme->user_id)
                ->first();
                //dd($user->id);
            if ($user) {

                Auth::login($user);

                $permissions = $this->authService->getUserPermissionsAssigned($user->id);
                //dd($permissions);
                session(['permissions' => $permissions]);

                return redirect('/dashboard');
            }
        }

        return redirect()->route('ubp.register', ['token' => $token]);
    }

       public function ubpUserList(){
        
        $title = 'UBP User List';

        return view('ubp.ubp-user-list',compact('title'));
    }

    public function getUbpUserList(){

        $result = $this->ubpservice->getUbpUserList();
        return $this->success($result);
    }
}
