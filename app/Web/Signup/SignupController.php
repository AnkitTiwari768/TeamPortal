<?php

declare(strict_types=1);

namespace App\Web\Signup;

use App\Traits\HasResponses;
use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Observers\AuditTrailLog;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Signup\{SignupService, SignupRequest};
use App\Http\Api\V1\User\User;
use App\Notifications\Email\Signup as SignupNotification;
use Session;
use Mail;
use DB;
use App\Web\VerifyOtp\VerifyOtpAction;
use App\Web\VerifyOtp\VerifyOtpDto;
use App\Web\VerifyOtp\VerifyOtpStatus;
use Illuminate\Http\JsonResponse;
use Mews\Captcha\Facades\Captcha;
use App\Http\Api\V1\Registration\{RegistrationService};




class SignupController
{
	use HasResponses;

	private static string $module = 'signup.index';

	public function __construct(
		private AuditTrailLog $auditTrailLog,
		private SignupService $signupService,
		private RegistrationService $RegistrationService
	) {
		$this->auditTrailLog = $auditTrailLog;
		$this->signupService = $signupService;
	}

	function captcha_check($value)
	{
		return Captcha::check($value);
	}

	public function udyamDetails(Request $request)
	{
		//UDYAM-HR-01-0008623 , 9996607064

		/*if (config('settings.enable_captcha')) {

				if (empty($request->udyam_no) || empty($request->mobile) || empty($request->captcha)) {
					return response()->json([
						'status' => false,
						'msg' => 'Please enter Udyam Registration No. , Mobile Number & Captcha'
					]);
				}

				if (!captcha_check($request->captcha)) {
					return response()->json([
						'status' => false,
						'msg' => 'Invalid captcha.Please enter correct captcha'
					]);
				}
			}*/


		if (empty($request->udyam_no)) {
			return response()->json([
				'status' => false,
				'msg' => 'Please Enter Udyam Registration No.'
			]);
		}

		if (empty($request->mobile)) {
			return response()->json([
				'status' => false,
				'msg' => 'Please Enter Mobile Number'
			]);
		}

		if (empty($request->captcha)) {
			return response()->json([
				'status' => false,
				'msg' => 'Please Enter Captcha'
			]);
		}


		if (config('settings.enable_captcha')) {
			$validator = Validator::make($request->all(), [
				'captcha' => 'required|captcha',
			]);

			if ($validator->fails()) {
				return response()->json([
					'status' => false,
					'msg' => 'Please enter correct captcha'
				]);
			}
		}



		$exists = DB::table('team_msme_schemes')->where('mobile', $request->mobile)->where('udyam_no', $request->udyam_no)->exists();

		if ($exists) {
			return response()->json([
				'status' => false,
				'msg'    => 'This Udyam number and mobile is already registered.',
			]);
		}

		if (!config('settings.udyam_api_by_pass')) {

			$udetails = $this->signupService->getUdyamDetails($request->udyam_no, $request->mobile);

			if (!empty($udetails['BasicDetail']['Error'])) {
				return response()->json([
					'status' => false,
					'msg' => 'This Udyam number and mobile invalid.',
				]);
			} else {

				if (strtolower($udetails['BasicDetail']['MajorActivity']) === 'trading') {
					return response()->json([
						'status' => false,
						'msg'    => 'Registration on the TEAMS Portal is currently restricted for MSMEs engaged in Trading activities.',
					]);
				}

				if (strtolower($udetails['BasicDetail']['EnterpriseType']) === 'medium') {
					return response()->json([
						'status' => false,
						'msg'    => 'Medium-scale MSMEs are not eligible for registration on the TEAMS Portal.',
					]);
				}

				return view('applicant_signup.udyam-details')
					->with('udetails', $udetails)
					->with('lists', (object) $this->signupService->getDropdownList());
			}
		} else {

			// DB::statement("SELECT delete_msme_by_udyam_no(?)", ['UDYAM-HR-01-0008623']);

			$response = '<UdyamDetail>
			<BasicDetail>
			<UdyamNo>UDYAM-HR-01-0008623</UdyamNo>
			<EnterpriseName>M/S AGN ENTERPRISES</EnterpriseName>
			<EntrepreneurName>M/S AGN ENTERPRISES</EntrepreneurName>
			<OrganisationType>Partnership</OrganisationType>
			<EmailId>agnenterprises4@gmail.com</EmailId>
			<SocialCategory>OBC</SocialCategory>
			<Gender>Male</Gender>
			<PH>No</PH>
			<CommunicationAddress>FIRST FLOOR, 2556/2, AMBALA, FIRST FLOOR, AMBALA, AMBALA</CommunicationAddress>
			<LG_ST_Code>6</LG_ST_Code>
			<State>HARYANA</State>
			<LG_DT_Code>58</LG_DT_Code>
			<District>AMBALA</District>
			<PINCode>133001</PINCode>
			<IncorporationDate>21/08/2021</IncorporationDate>
			<MajorActivity>Manufacturing</MajorActivity>
			<EnterpriseType>Micro</EnterpriseType>
			<EnterpriseDetail>
			<EType CL_Year="2025-26" CL_Date="01/04/2025" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2024-25" CL_Date="19/09/2024" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2023-24" CL_Date="18/09/2024" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2022-23" CL_Date="26/06/2022" EnterpriseType="A" EnterpriseTypee="Micro"/>
			<EType CL_Year="2021-22" CL_Date="22/10/2021" EnterpriseType="A" EnterpriseTypee="Micro"/>
			</EnterpriseDetail>
			<TotalEmp>4</TotalEmp>
			<AppliedDate>22/10/2021</AppliedDate>
			</BasicDetail>
			<ActivityDetail>
			<Activities>
			<Two_DigitActivity>26 - Manufacture of computer, electronic and optical products</Two_DigitActivity>
			<Four_DigitActivity>2670 - Manufacture of optical instruments and equipment</Four_DigitActivity>
			<Five_DigitActivity>26700 - Manufacture of optical instruments and equipment</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>25 - Manufacture of fabricated metal products, except machinery and equipment</Two_DigitActivity>
			<Four_DigitActivity>2593 - Manufacture of cutlery, hand tools and general hardware</Four_DigitActivity>
			<Five_DigitActivity>25932 - Manufacture of hand tools (non-power-driven) for agricultural/horticulture/forestry</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>22 - Manufacture of rubber and plastics products</Two_DigitActivity>
			<Four_DigitActivity>2220 - Manufacture of plastics products</Four_DigitActivity>
			<Five_DigitActivity>22209 - Manufacture of other plastics products n.e.c</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>23 - Manufacture of other non-metallic mineral products</Two_DigitActivity>
			<Four_DigitActivity>2310 - Manufacture of glass and glass products</Four_DigitActivity>
			<Five_DigitActivity>23104 - Manufacture of laboratory or pharmaceutical glassware</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>27 - Manufacture of electrical equipment</Two_DigitActivity>
			<Four_DigitActivity>2720 - Manufacture of batteries and accumulators</Four_DigitActivity>
			<Five_DigitActivity>27201 - Manufacture of primary cells and primary batteries nd rechargable batteries, cells containing manganese oxide, mercuric oxide silver oxide or other material</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>27 - Manufacture of electrical equipment</Two_DigitActivity>
			<Four_DigitActivity>2790 - Manufacture of other electrical equipment</Four_DigitActivity>
			<Five_DigitActivity>27900 - Manufacture of other electrical equipment</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>28 - Manufacture of machinery and equipment n.e.c.</Two_DigitActivity>
			<Four_DigitActivity>2815 - Manufacture of ovens, furnaces and furnace burners</Four_DigitActivity>
			<Five_DigitActivity>28150 - Manufacture of ovens, furnaces and furnace burners</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>31 - Manufacture of furniture</Two_DigitActivity>
			<Four_DigitActivity>3100 - Manufacture of furniture</Four_DigitActivity>
			<Five_DigitActivity>31009 - Manufacture of other furniture n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32502 - Manufacture of laboratory apparatus (laboratory ultrasonic cleaning machinery, laboratory sterilizers, laboratory type distilling apparatus, laboratory centrifuges etc.)</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32505 - Manufacture of measuring instruments suc as thermometers etc.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3250 - Manufacture of medical and dental instruments and supplies</Four_DigitActivity>
			<Five_DigitActivity>32509 - Manufacture of other medical and dental instruments n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>32 - Other manufacturing</Two_DigitActivity>
			<Four_DigitActivity>3290 - Other manufacturing n.e.c.</Four_DigitActivity>
			<Five_DigitActivity>32909 - Manufacture of other articles n.e.c.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8510 - Primary education</Four_DigitActivity>
			<Five_DigitActivity>85109 - Other primary education activities n.e.c.</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8521 - General secondary education</Four_DigitActivity>
			<Five_DigitActivity>85211 - General school education in the first stage of the secondary level (up to X th standard) without any special subject pre-requisite</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8521 - General secondary education</Four_DigitActivity>
			<Five_DigitActivity>85212 - General school education in the second stage of the secondary level (Senior/ Higher secondary) giving, in principle, access to higher education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8522 - Technical and vocational secondary education</Four_DigitActivity>
			<Five_DigitActivity>85222 - Technical and vocational education for handicapped students below the level of higher education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8530 - Higher education</Four_DigitActivity>
			<Five_DigitActivity>85301 - Higher education in science, commerce, humanity and fine arts leading to a university degree or equivalent</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8542 - Cultural education</Four_DigitActivity>
			<Five_DigitActivity>85420 - Cultural education</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8549 - Other education n.e.c.</Four_DigitActivity>
			<Five_DigitActivity>85499 - Other educational services n.e.c.</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>85 - Education</Two_DigitActivity>
			<Four_DigitActivity>8550 - Educational support services</Four_DigitActivity>
			<Five_DigitActivity>85500 - Educational support services</Five_DigitActivity>
			<Activity>Services</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>26 - Manufacture of computer, electronic and optical products</Two_DigitActivity>
			<Four_DigitActivity>2651 - Manufacture of measuring, testing, navigating and control equipment</Four_DigitActivity>
			<Five_DigitActivity>26516 - Manufacture of laboratory analytical instruments and miscellaneous laboratory apparatus for measuring and testing such as scales, balances, incubators etc.</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			<Activities>
			<Two_DigitActivity>28 - Manufacture of machinery and equipment n.e.c.</Two_DigitActivity>
			<Four_DigitActivity>2829 - Manufacture of other special-purpose machinery</Four_DigitActivity>
			<Five_DigitActivity>28292 - Manufacture of machinery for working soft rubber or plastics or for the manufacture of products of these materials</Five_DigitActivity>
			<Activity>Manufacturing</Activity>
			</Activities>
			</ActivityDetail>
			</UdyamDetail>';

			$xmlObject = simplexml_load_string($response);
			$json = json_encode($xmlObject);
			$udetails = json_decode($json, true);
			//dd($udetails);

			if (strtolower($udetails['BasicDetail']['MajorActivity']) === 'trading') {
				return response()->json([
					'status' => false,
					'msg'    => 'Registration on the TEAMS Portal is currently restricted for MSMEs engaged in Trading activities.',
				]);
			}

			if (strtolower($udetails['BasicDetail']['EnterpriseType']) === 'medium') {
				return response()->json([
					'status' => false,
					'msg'    => 'Medium-scale MSMEs are not eligible for registration on the TEAMS Portal.',
				]);
			}

			return view('applicant_signup.udyam-details')
				->with('udetails', $udetails)
				->with('lists', (object) $this->signupService->getDropdownList());
		}
	}

	public function index(): View
	{

		$title = __('Create Your Account');
		crypto_secrets();
		return view('applicant_signup.signup', compact('title'))
			->with('crypto_salt', session('crypto_salt'))
			->with('crypto_iv', session('crypto_iv'))
			->with('crypto_key', session('crypto_key'))
			->with('crypto_key_size', session('crypto_key_size'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('lists', (object) $this->signupService->getDropdownList());
	}


	public function create(Request $request)
	{
		$validator = Validator::make($request->all(), SignupRequest::getRules($request), SignupRequest::messages());

		if ($validator->fails()) {
			return $this->error($validator->errors());
		}

		$this->signupService->store($request->all());
		//$result = $this->signupService->store($request->all());
		return $this->success(message: 'MSME Registration Successfully');
	}


	public function snpDetails(Request $request)
	{
		$state_id = $this->signupService->getStateId('states', $request->state_code);
		// $snpdetails = $this->signupService->getSnpDetails($state_id, $request->ondc_transaction_type_id, $request->sub_domain);
		$snpdetails = $this->RegistrationService->getSelectSnpDetails($request->state_id, $request->ondc_transaction_type_id, $request->sub_domain);
		//echo "<pre/>";print_r($snpdetails);exit;
		return view('applicant_signup.snp-details')->with('snpdetails', $snpdetails);
	}


	/*public function create(Request $request,VerifyOtpAction $action)
    {
		$validated = $request->validate([
			'username' => 'required',
			'otp'      => 'required',
		]);

		$verifyOtpStatus = $action->execute(VerifyOtpDto::fromArray($validated));

        if ($verifyOtpStatus === VerifyOtpStatus::CODE_INVALID || $verifyOtpStatus === VerifyOtpStatus::CODE_EXPIRED) {
            return $this->expiredOrInvalidOtp();
        }

		$validator = Validator::make($request->all(), SignupRequest::getRules($request), SignupRequest::messages());
        
        if ($validator->fails()) 
        {
            return $this->error($validator->errors());
        }
		
		$this->signupService->store($request->all());
		//$result = $this->signupService->store($request->all());
         return $this->success(message: 'MSME Registration Successfully');
		
    }*/
}
