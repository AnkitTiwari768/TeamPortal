<?php

declare(strict_types=1);

namespace App\Web\SNP;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Registration\{RegistrationService, UdyamRequest};
use DateTime;
use DB;


class SNPMSMEController extends ClientController
{
	private static string $module = 'snp.index';

	public function __construct(private SNPMSMEService $service, private RegistrationService $rservice) {}


	public function msmeForMe(): View
	{
		if (!acl('View-open-msme')) {
			abort(403, 'You do not have permission to access this page.');
		}
		return view('snp.mymsme')->with('title', 'Open MSE')
			->with('lists', (object) $this->service->getDropdownList());
	}


	public function getmyMSMEList()
	{
		return $this->success($this->service->getmyMSMEList());
	}

	public function msmeChoosenMe(): View
	{
		 if (!acl('View-selected-msme')) {
			abort(403, 'You do not have permission to access this page.');
		}
		return view('snp.msmechoosenme')
			->with('title', 'Direct Selection By MSE')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getmsmeChoosenMeList()
	{
		return $this->success($this->service->getmyCghoosenMSMEList($select_snp = 1, $is_msmeregistration = 1, $status = 0)); //added select_snp and is_msmeregistration after new code
	}


	public function onboardedMSME(): View
	{
		 if (!acl('View-onboarded-msme')) {
			abort(403, 'You do not have permission to access this page.');
		}
		return view('snp.onboardedmsme')
			->with('title', 'Onboarded MSE')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getmyOnboardedMSMEList()
	{
		return $this->success($this->service->getmyOnboardedMSMEList($select_snp = null, $is_msmeregistration = 2, $status = 1)); //added select_snp and is_msmeregistration after new code
	}


	public function mseToBeValidated(): View
	{
		return view('snp.mse_to_be_validated')
			->with('title', 'MSE To Be Validated')
			->with('lists', (object) $this->service->getDropdownList())
			->with('module_url', 'mse-to-be-validated');
	}

	public function getMseToBeValidatedList()
	{
		return $this->success($this->service->getMseToBeValidatedList($select_snp = 1, $is_msmeregistration = 1, $status = 0));
	}


	public function validatedMse(): View
	{
		return view('snp.validated_mse')
			->with('title', 'Validated MSE')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getValidatedMseList()
	{
		return $this->success($this->service->getValidatedMseList($select_snp = null, $is_msmeregistration = 2, $status = 0));
	}


	public function mseSelfRegistration(): View
	{
		return view('snp.mse_self_registration_on_udyam')
			->with('title', 'MSE Self Registration On Udyam')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getMseSelfRegistrationList()
	{
		return $this->success($this->service->getMseSelfRegistrationAndSeekDeskSupportList($select_snp = 2, $is_msmeregistration = 1, $status = 0));
	}


	public function mseSeeksHelpDeskSupport(): View
	{
		return view('snp.mse_seeks_helpdesk_support')
			->with('title', 'MSE seeks Helpdesk Support')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getmseSeeksHelpDeskSupportList()
	{
		return $this->success($this->service->getMseSelfRegistrationAndSeekDeskSupportList($select_snp = 3, $is_msmeregistration = 1, $status = 0));
	}


	public function secondStep($msmeId): View
	{

		$detail = $this->service->getMseDetails($msmeId);
		//dd($detail);
		return view('snp.secondstep')
			->with('title', 'Mse to be validated')
			->with('detail', $detail)
			->with('lists', (object) $this->service->getDropdownList());
	}


	public function udyamSnpDetails(Request $request)
	{
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


		if (!config('settings.udyam_api_by_pass')) {
			$udetails = $this->rservice->getUdyamDetails($request->udyam_no, $request->mobile);
			//$detail=$this->service->getMseDetailsByUdyaMMobile($request->udyam_no,$request->mobile);

			$detail = $this->service->getMseDetails($request->msmeId);

			if (!empty($udetails['BasicDetail']['Error'])) {
				return response()->json([
					'status' => false,
					'msg' => 'This Udyam number and mobile invalid.',
				]);
			} else {
				return view('snp.udyam-details')
					->with('detail', $detail)
					->with('udetails', $udetails)
					->with('lists', (object) $this->rservice->getDropdownList());
			}
		} else {

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
			//$detail=$this->service->getMseDetailsByUdyaMMobile($request->udyam_no,$request->mobile);
			//dd($udetails);
			$detail = $this->service->getMseDetails($request->msmeId);
			return view('snp.udyam-details')
				->with('udetails', $udetails)
				->with('detail', $detail)
				->with('lists', (object) $this->rservice->getDropdownList());
		}
	}



	public function update(Request $request, $msmeId)
	{

		$validator = Validator::make($request->all(), UdyamRequest::getRules($request), UdyamRequest::messages());

		if ($validator->fails()) {
			return $this->error($validator->errors());
		}

		$this->UdyamDetailUpdate($request->all(), $msmeId);
		return $this->success(message: 'MSME Registration Successfully Completed');
	}



	public function UdyamDetailUpdate(array $payload, ?string $id): bool
	{
		if (!config('settings.udyam_api_by_pass')) {
			$udetails = $this->rservice->getUdyamDetails($payload['udyam_no'], $payload['mobile']);
		} else {
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
		}

		return \DB::transaction(function () use ($payload, $udetails, $id) {

			$IncorporationDate = DateTime::createFromFormat('m/d/Y', $udetails['BasicDetail']['IncorporationDate']);

			$msmeData = [
				'udyam_no' => $udetails['BasicDetail']['UdyamNo'],
				'mobile' => $payload['mobile'],
				'email' => $udetails['BasicDetail']['EmailId'],
				'enterprise_name' => $udetails['BasicDetail']['EnterpriseName'],
				'entrepreneur_name' => $udetails['BasicDetail']['EntrepreneurName'],
				'organisation_type' => $udetails['BasicDetail']['OrganisationType'],
				'address' => $udetails['BasicDetail']['CommunicationAddress'],
				'pincode' => $udetails['BasicDetail']['PINCode'],
				'ph' => $udetails['BasicDetail']['PH'],
				'major_activity' => $udetails['BasicDetail']['MajorActivity'],
				'msme_classification' => $udetails['BasicDetail']['EnterpriseType'],
				'gender' => $udetails['BasicDetail']['Gender'],
				'social_category' => $udetails['BasicDetail']['SocialCategory'],
				'incorporation_date' => $IncorporationDate->format('Y-m-d'),
				'total_emp' => $udetails['BasicDetail']['TotalEmp'],
				'state_id' => $this->getStateId('states', $udetails['BasicDetail']['LG_ST_Code']),
				'district_id' => $this->getStateId('locations', $udetails['BasicDetail']['LG_DT_Code']),
				'enterprise_details' => json_encode($udetails['BasicDetail']['EnterpriseDetail']),
				'activity_details' => json_encode($udetails['ActivityDetail']),
				'gstin_no' => $payload['gstin_no'],
				'pan_no' => $payload['pan_no'],
				'turnover' => $payload['turnover'],
				'current_state_business_id' => $payload['current_state_business_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'product_category_id' => json_encode($payload['product_category_id']),
				'attending_ondc_awareness_workshop' => $payload['attending_ondc_awareness_workshop'],
				'status' => 1,
				'is_msme_registration' => 2,
				'updated_at' => currentDateTime()
			];

			$result = DB::table('team_msme_schemes')->where('id', $id)->update($msmeData);
			if ($result) {
				$snpId = $this->rservice->getSnpDetails(AuthId());
				DB::table('team_snpmsme_mapping')->updateOrInsert(
					['msme_id' => $id], // condition
					[
						'id' => uuid(),
						'snp_id' => $snpId,
						'created_at' => currentDateTime(),
						'updated_at' => currentDateTime()
					]
				);

				return true;
			} else {
				return false;
			}
		});
	}




	public function onboardMSME(): View //added for new listing
	{
		return view('snp.onboardmsme')
			->with('title', 'Onboard MSE')
			->with('lists', (object) $this->service->getDropdownList());
	}

	public function getmyOnboardMSMEList() //added for new listing
	{
		return $this->success($this->service->getmyOnboardedMSMEList($select_snp = null, $is_msmeregistration = 2, $status = 1));
	}

	public function msmeInprogress($msmeId): View
	{
		$detail = $this->getMsmeDetails($msmeId);
		crypto_secrets();
		return view('snp.msme-inprogress')
			->with('detail', $detail)
			->with('crypto_salt', session('crypto_salt'))
			->with('crypto_iv', session('crypto_iv'))
			->with('crypto_key', session('crypto_key'))
			->with('crypto_key_size', session('crypto_key_size'))
			->with('crypto_iterations', session('crypto_iterations'))
			->with('title', 'MSME Inprogress');
	}


	public function getMsmeDetails($msmeId)
	{
		return DB::table('team_msme_schemes as ms')->select('ms.id', 'ms.mobile', 'ms.email')->where('ms.id', $msmeId)->first();
	}


	public function getStateId($table, $state_code)
	{
		return DB::table($table)->where('code', $state_code)->first()->id;
	}
}
