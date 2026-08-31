<?php

namespace App\Http\Api\V1\UdyamBharatPortalIntergation;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Domain\UbpIntegration\UbpService;
use App\Domain\UbpIntegration\UbpTokenService;
use Illuminate\Support\Facades\Validator;
use App\Domain\UbpIntegration\UbpUdyamService;
use App\Models\User;

class UBPController extends Controller
{
    protected $ubpService;
    protected $udyamService;
    public function __construct(UbpService $ubpService, UbpUdyamService $udyamService)
    {
        $this->ubpService = $ubpService;
        $this->udyamService = $udyamService;
    }

    public function getToken(Request $request)
    {
        $clientId = $request->input('client_id');
        $clientSecret = $request->input('client_secret');

        $validator = Validator::make($request->all(), [
            'client_id' => 'required|string',
            'client_secret' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'client_id and client_secret are mandatory.',
                'errors' => $validator->errors()
            ], 422);
        }

        $tokenData = $this->ubpService->generateToken($clientId, $clientSecret);

        if (!$tokenData) {
            return response()->json(['error' => 'Invalid Credentials'], 401);
        }

        return response()->json($tokenData);
    }

        
    public function registerMsme(Request $request, UbpTokenService $tokenService)
    {
        try {

            $validator = Validator::make($request->all(), [
                'udyam_no' => 'required|string',
                'mobile'   => 'required|digits:10'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'type'    => 'validation_error',
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }

            $udyamNo = $request->udyam_no;
            $mobile  = $request->mobile;

            if (!config('settings.udyam_api_by_pass_udyam_bharat_portal')) {

                $udyamData = $this->udyamService->fetchDetails($udyamNo, $mobile);

                if (!$udyamData) {
                    return response()->json([
                        'status'  => false,
                        'type'    => 'server_error',
                        'message' => 'Udyam API is not working. Please try later.'
                    ], 503);
                }

                if (data_get($udyamData, 'BasicDetail.ErrorCode') == "1") {
                    return response()->json([
                        'status'  => false,
                        'type'    => 'validation_error',
                        'message' => data_get($udyamData, 'BasicDetail.Error') ?? 'Invalid Udyam details'
                    ], 422);
                }
            }

            $user = User::where('mobile', $mobile)->first();

            if ($user) {
                $token = $tokenService->generateUserToken([
                    'udyam_no' => $udyamNo,
                    'mobile'   => $mobile,
                ]);

                return response()->json([
                    'status'  => true,
                    'type'    => 'existing_user',
                    'message' => 'User already exists',
                    'data'    => [
                        'redirect_url' => url('/ubp/login?token=' . $token),
                    ]
                ]);
            }

            

            $token = $tokenService->generateUserToken([
                'udyam_no' => $udyamNo,
                'mobile'   => $mobile,
            ]);

            return response()->json([
                'status'  => true,
                'type'    => 'success',
                'message' => 'Udyam verified successfully',
                'data'    => [
                    'redirect_url' => url('/ubp/login?token=' . $token),
                    'token'        => $token
                ]
            ]);

        } catch (\Exception $e) {

            \Log::error('Udyam API Error', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'status'  => false,
                'type'    => 'exception',
                'message' => 'Udyam API temporarily unavailable'
            ], 500);
        }
    }

}
