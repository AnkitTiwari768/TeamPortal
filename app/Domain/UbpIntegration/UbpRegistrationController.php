<?php

namespace App\Domain\UbpIntegration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Api\V1\Registration\RegistrationService;
use App\Http\Api\V1\Registration\RegistrationRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Api\V1\Auth\AuthService;
use Illuminate\Support\Facades\Validator;
use App\Contracts\GrantType;
use Illuminate\Support\Facades\DB;

class UbpRegistrationController extends Controller
{
    public function __construct(
       protected UbpTokenService $tokenService,
       protected UbpUdyamService $udyamService,
       protected RegistrationService $registrationService,
       protected AuthService $authService,
       protected UbpService $ubpService
        )
    {
    }

    public function show(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            abort(400, 'Token missing');
        }

        $payload = $this->tokenService->decryptUserToken($token);

        if (!$payload) {
            abort(401, 'Invalid Token');
        }

        $udyamNo = $payload['udyam_no'];
        $mobile  = $payload['mobile'];

        $udetails = [];

        if(!config('settings.udyam_api_by_pass_udyam_bharat_portal')){
            try{

                $udyamData = $this->udyamService->fetchDetails($udyamNo, $mobile);

                if ($udyamData) {
                    $udetails = $udyamData;
                }

            } 
            catch (\Exception $e) {
                
                session()->flash('error','Udyam API temporarily unavailable');
            }
        }

        if(empty($udetails)){
            $udetails = [
                'BasicDetail' => [
                    'UdyamNo' => $udyamNo,
                    'EnterpriseName' => '',
                    'EntrepreneurName' => '',
                    'EmailId' => $payload['email'] ?? '',
                    'OrganisationType' => '',
                    'CommunicationAddress' => '',
                    'State' => '',
                    'District' => '',
                    'EnterpriseType' => '',
                    'MajorActivity' => '',
                    'LG_ST_Code' => '',
                    'LG_DT_Code' => ''
                ]
            ];
        }

        $listsArray = $this->registrationService->getDropdownList();
        $lists = (object)$listsArray;
        $email = $payload['email'] ?? '';

        return view('ubp.msme_registration',
            compact('udetails', 'lists', 'token', 'mobile', 'email','udyamNo')
        );
    }   

    public function store(Request $request)
    {
        $token = $request->input('token');
        //dd($token);
        if (!$token) {
            return response()->json(['status' => false, 'message' => 'Token missing', 'errors' => ['token' => ['Token missing']]]);
        }

        $payload = $this->tokenService->decryptUserToken($token);
        //dd($request->all());
        if (!$payload) {
            return response()->json(['status' => false, 'message' => 'Invalid Token', 'errors' => ['token' => ['Invalid Token']]]);
        }
        $request->merge([
            'udyam_no' => $payload['udyam_no'],
            'mobile' => $request->input('mobile'),
        ]);

        $validator = Validator::make($request->all(),UbpRegistrationRequest::getRules($request),UbpRegistrationRequest::messages());
  
        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()]);
        }

        try {

            $udetails = $this->udyamService->fetchDetails(
                $payload['udyam_no'],
                $payload['mobile']
            );

            $majorActivity = strtolower(trim($udetails['BasicDetail']['MajorActivity'] ?? ''));
            $enterpriseType = strtolower(trim($udetails['BasicDetail']['EnterpriseType'] ?? ''));

            if ($majorActivity === 'trading') {
                return response()->json([
                    'status' => false,
                    'message' => 'Registration on the TEAMS Portal is currently restricted for MSMEs engaged in Trading activities.'
                ]);
            }

            if ($enterpriseType === 'small') {
                return response()->json([
                    'status' => false,
                    'message' => 'Small-scale MSMEs are not eligible for registration on the TEAMS Portal.'
                ]);
            }

            $registrationType = $this->ubpService->store([
                ...$request->all(),
                'is_user_sso' => 1,
                'sso_type' => 1
            ]);

            // Common user fetch
            $user = User::where('mobile', $request->input('mobile'))->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'User not found after registration'
                ], 404);
            }

            // Common role assignment
            $roleId = DB::table('roles')
                ->where('slug', 'msme')
                ->value('id');

            DB::table('user_roles')->updateOrInsert(
                [
                    'user_id' => $user->id,
                    'role_id' => $roleId
                ],
                [
                    'type' => GrantType::THROUGH_ROLE
                ]
            );

            // Common login
            Auth::login($user);

            $permissions = $this->authService
                ->getUserPermissionsAssigned($user->id);

            session(['permissions' => $permissions]);

            // Response by registration type
            if ((int)$registrationType == 1) {
                return response()->json([
                    'status' => true,
                    'registration_type' => 1,
                    'message' => 'Your account has been created successfully. Now relevant SNP will connect with you for further process.',
                    'redirect' => url('dashboard')
                ]);
            }

            if ((int)$registrationType == 0) {
                return response()->json([
                    'status' => true,
                    'registration_type' => 0,
                    'message' => 'You are logged in successfully.',
                    'redirect' => url('dashboard')
                ]);
            }

            return response()->json([
                'status' => false,
                'message' => 'Invalid registration type'
            ], 400);

        } 

        catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage(), 'errors' => ['error' => [$e->getMessage()]]]);
        }
    }
}
