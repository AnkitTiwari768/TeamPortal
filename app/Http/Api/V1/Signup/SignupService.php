<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Signup;

use App\Traits\DataTable;
use App\Http\Services\CommonService;
use App\Http\Api\V1\User\User;
use App\Http\Api\V1\Signup\Signup;
use App\Contracts\GrantType;
use Illuminate\Support\Facades\DB;
use Mail;
use App\Traits\HasAttribute;
use Illuminate\Support\Facades\Http;
use App\Domain\EmailTemplate\EmailTemplateService;
use DateTime;
use App\Web\Notification\SendNotificationEvent;
use Illuminate\Support\Facades\Auth;


class SignupService
{
	use DataTable, HasAttribute;

	public function getUdyamDetails($udyam_no, $mobile)
	{
		//$response = Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,b2bmrt-VGVzdEBoeXc2MA==");
		
		$udyam_token=config('constant.UDYAM_TOKEN');		
		$response = Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,$udyam_token");

		if ($response->successful()) {
			$xml = $response->body();
			$xmlObject = simplexml_load_string($xml);
			$json = json_encode($xmlObject);
			//echo "<pre/>";print_r(json_decode($json, true));exit;
			return json_decode($json, true);
		} else {
			return response()->json([
				'error' => 'Failed to retrieve data',
				'status' => $response->status()
			]);
		}
	}

	/* public function store(array $payload, ?string $roleId = null): bool
    {
	
	$udetails=$this->getUdyamDetails($payload['udyam_no'],$payload['mobile']);
	//echo "<pre/>";print_r($udetails);exit;
	
	return DB::transaction(function() use ($payload,$udetails) {
		
		$uuid=uuid();
		$userId=uuid();
		$IncorporationDate = DateTime::createFromFormat('m/d/Y', $udetails['BasicDetail']['IncorporationDate']);
		$msmeData = [
				'id'=>$uuid,
				'user_id'=>$userId,
				'team_id'=>'TEAM'.rand(10000, 99999),
				'udyam_no'=>$udetails['BasicDetail']['UdyamNo'],
				'mobile' => $payload['mobile'],
				'email' => $udetails['BasicDetail']['EmailId'],
				'enterprise_name'=> $udetails['BasicDetail']['EnterpriseName'],
				'entrepreneur_name'=> $udetails['BasicDetail']['EntrepreneurName'],
				'organisation_type'=> $udetails['BasicDetail']['OrganisationType'],
				'address'=> $udetails['BasicDetail']['CommunicationAddress'],
				'pincode'=> $udetails['BasicDetail']['PINCode'],
				'ph'=> $udetails['BasicDetail']['PH'],
				'major_activity' => $udetails['BasicDetail']['MajorActivity'],
				'msme_classification' => $udetails['BasicDetail']['EnterpriseType'],
				'gender' => $udetails['BasicDetail']['Gender'],
				'social_category' => $udetails['BasicDetail']['SocialCategory'],
				'incorporation_date' =>$IncorporationDate->format('Y-m-d'),
				//'incorporation_date' =>date('Y-m-d',strtotime($udetails['BasicDetail']['IncorporationDate'])),
				'total_emp' => $udetails['BasicDetail']['TotalEmp'],
				'state_id' => $this->getStateId('states',$udetails['BasicDetail']['LG_ST_Code']),
				'district_id' =>$this->getStateId('locations',$udetails['BasicDetail']['LG_DT_Code']),
				'enterprise_details' => json_encode($udetails['BasicDetail']['EnterpriseDetail']),
				'activity_details' => json_encode($udetails['ActivityDetail']),
				
				'gstin' => $payload['gstin'],
				'gstin_no' => $payload['gstin_no'],
				
				'pan' =>$payload['pan'],
				'pan_no' => $payload['pan_no'],
				
				'nic_code' => $payload['nic_code'],
				
				'specially_abled' => $payload['specially_abled'],
				
				'net_investment_plant_machinery' => $payload['net_investment_plant_machinery'],
				'turnover' => $payload['turnover'],
				'dic_attached' => $payload['dic_attached']??null,
				'current_state_business_id' => $payload['current_state_business_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'product_category_id' => json_encode($payload['product_category_id']),
								
				'select_snp' => $payload['select_snp'],
				
				'physical_device_business_transactions' => $payload['physical_device_business_transactions'],
				
				'printer' => $payload['printer'],

				'catalogue_prodcut_details' => $payload['catalogue_prodcut_details'],
				
				'attending_ondc_awareness_workshop' => $payload['attending_ondc_awareness_workshop'],
								
				'products_geography' => $payload['products_geography']??null,
				'status'=> 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			];
	
			DB::table('team_msme_schemes')->insert($msmeData);
			
			$user_mapping=[
				'id'=>$userId,
				'username'=>$udetails['BasicDetail']['EmailId'],
				'mobile'=> $payload['mobile'],
				'email'=> $udetails['BasicDetail']['EmailId'],
				'status'=> 1,
				'is_msme'=>1,
				'created_at'=>currentDateTime()
			];
				
			DB::table('users')->insert($user_mapping);
			
			if($payload['select_snp']==1){
				$snp_mapping=[
					'id'=>uuid(),
					'snp_id'=>$payload['snp_id'],
					'msme_id'=>$uuid,
					'created_at'=>currentDateTime()
				];
				
				DB::table('team_snpmsme_mapping')->insert($snp_mapping);
			}
		
			return true; 
			
		});
		
    } */


	public function store(array $payload, ?string $roleId = null): bool
	{
		if (!config('settings.udyam_api_by_pass')) {
			$udetails = $this->getUdyamDetails($payload['udyam_no'], $payload['mobile']);
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

		//echo "<pre/>";print_r($udetails);exit;

		return DB::transaction(function () use ($payload, $udetails) {

			$uuid = uuid();
			$userId = uuid();
			$IncorporationDate = DateTime::createFromFormat('m/d/Y', $udetails['BasicDetail']['IncorporationDate']);
			$msmeData = [
				'id' => $uuid,
				'user_id' => $userId,
				//'team_id' => 'TEAM' . rand(10000, 99999),
				'team_id' =>  getNextTeamId() ?: null,
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

				//'gstin' => $payload['gstin'],
				'gstin_no' => $payload['gstin_no'],

				//'pan' =>$payload['pan'],
				'pan_no' => $payload['pan_no'],

				//'nic_code' => $payload['nic_code'],

				//'specially_abled' => $payload['specially_abled'],

				//'net_investment_plant_machinery' => $payload['net_investment_plant_machinery'],
				'turnover' => $payload['turnover'],
				//'dic_attached' => $payload['dic_attached']??null,
				'current_state_business_id' => $payload['current_state_business_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'product_category_id' => json_encode($payload['product_category_id']),

				'select_snp' => $payload['select_snp'],

				//'physical_device_business_transactions' => $payload['physical_device_business_transactions'],

				//'printer' => $payload['printer'],

				//'catalogue_prodcut_details' => $payload['catalogue_prodcut_details'],

				'attending_ondc_awareness_workshop' => $payload['attending_ondc_awareness_workshop'],

				//'products_geography' => $payload['products_geography']??null,
				'status' => 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			];

			DB::table('team_msme_schemes')->insert($msmeData);

			$user_mapping = [
				'id' => $userId,
				'username' => $udetails['BasicDetail']['EmailId'],
				'first_name' => $udetails['BasicDetail']['EnterpriseName'],
				'mobile' => $payload['mobile'],
				'email' => $udetails['BasicDetail']['EmailId'],
				'status' => 1,
				'is_msme' => 1,
				'created_at' => currentDateTime()
			];

			DB::table('users')->insert($user_mapping);

			$roleId = DB::table('roles')->where('slug', 'msme')->value('id');

			DB::table('user_roles')->insert(['user_id' => $userId, 'role_id' => $roleId, 'type' => GrantType::THROUGH_ROLE]);
		
			if ($payload['select_snp'] == 1) {
				
				$snp_mapping = [
					'id' => uuid(),
					'snp_id' => $payload['snp_id'],
					'msme_id' => $uuid,
					'created_at' => currentDateTime()
				];

				DB::table('team_snpmsme_mapping')->insert($snp_mapping);

				$snpName = DB::table('team_snp_scheme')->where('id', $payload['snp_id'])->value('snp_name');

				// send notification to new msme for available snp direct list
			
			
				$organizationName = DB::table('team_snp_scheme')->where('user_id', $payload['user_id'] ?? null)->value('organization_name');

				app(EmailTemplateService::class)->send(
					templateKey: 'snp-selection',
					toEmail: $msmeData['email'],
					data: [
						'snp_name' => $organizationName ? $organizationName : $snpName,
						'helpdesk_number' => config('settings.helpdesk_number'),
						'email' =>  config('settings.helpdesk_email'),
						'year' => (string) date('Y')
					]
				);
			}
			
			$fromUserId = authId();
			$fromRoleId = DB::table('user_roles')->where('user_id', $fromUserId)->value('role_id');
			$toRoleId = DB::table('roles')->where('slug', 'snp')->value('id');
		
			if ($payload['select_snp'] == 0) {
					// send notification to new msme for available snp open list
					event(new SendNotificationEvent(templateKey: 'new-mse-added-open-list',fromUserId: $fromUserId,toUserId:null,formRole: $fromRoleId,toRole: $toRoleId,type: 2, 
					message: [
						'MSE_NAME' => $msmeData['enterprise_name']
					]));
				}
				else
				{
					// send notification to new msme for available snp direct list
					event(new SendNotificationEvent(templateKey: 'new-mse-available-direct-list',fromUserId: $fromUserId, toUserId:null, formRole: $fromRoleId, toRole: $toRoleId, type: 2, 
					message: [
						'MSE_NAME' => $msmeData['enterprise_name']
					]));
			    }
			// send credentials  to mail    
			app(EmailTemplateService::class)->send(
				templateKey: 'msme-registration',
				toEmail: $msmeData['email'],
				data: [
					'msme_name' => $msmeData['enterprise_name'],
					'helpdesk_number' => config('settings.helpdesk_number'),
					'email' => config('settings.helpdesk_email'),
					'team_registration_id' => $msmeData['team_id'],
					'year' => (string) date('Y')
				]
			);

			return true;
		});
	}


	public function getStateId($table, $state_code)
	{
		return DB::table($table)->where('code', $state_code)->first()->id;
	}



	public function getSnpDetails($state_id, $transaction_type, $sub_domain)
	{
		$query = DB::table('team_snp_scheme')->select('id', 'snp_name', 'commercial_model', 'commercial_model_document', 'live_seller', 'date_of_going_live_on_ondc', 'no_of_transactions_done', 'short_description', 'description_document')
			->where('status', 1)
			->whereRaw("JSON_CONTAINS(state_id, ?)", ['"' . $state_id . '"'])
			->whereRaw("JSON_CONTAINS(transaction_type, ?)", ['"' . $transaction_type . '"']);

		if (!empty($sub_domain)) {
			$query->where(function ($q) use ($sub_domain) {
				foreach ($sub_domain as $domain) {
					$q->orWhereRaw("JSON_CONTAINS(sub_domain, ?)", ['"' . $domain . '"']);
				}
			});
		}

		$data = $query->get();

		return $data;
	}


	public function getDropdownList()
	{
		$commonService = new CommonService();
		return [
			'states' => $commonService->getDropdownNewList('states', 'status', 'ASC', 'name', array('id', 'name')),
			//'orgnisation_types'=>$this->listOf('type-of-organization'),
			//'major_activities'=>$this->listOf('major-activity-of-unit'),
			//'msme_classifications'=>$this->listOf('meme-classification'),
			//'gender' => $commonService->getGender(),
			//'social_categories' => $commonService->socialCategory(),
			'current_state_business' => $this->listOf('current-state-of-your-business'),
			'ondc_types' => $this->listOf('types-of-transactions-preferred-on-ondc'),
			'sub_domains' => $commonService->getDropdownNewList('sub_domains', 'status', 'ASC', 'name', array('id', 'name')),

			'yesno' => $commonService->getYesNoStatus(),
			'status' => $commonService->getStatus()
		];
	}


	/*
	public function store(array $payload, ?string $roleId = null): bool
    {
	
	return DB::transaction(function() use ($payload) {
		$commonService = new CommonService();
		$userData = [
		    'id'=>uuid(),
			'first_name' => $payload['entrepreneur_name'] ?? null,
			'mobile'=>      $payload['mobile'],
			'username'=> 	 $payload['username']??null,
			'email'=> 		 $payload['username']??null,
			'password'=>     '$2y$10$Ed.jlvGlo89FDQL7cGxGG.QOu6bVcSnZ/SqvuUXuHlGJ6hnsWBAvO',
			'status'  =>      1 ,
			'is_role_mapped'=> 1
		];
	
		$userId = User::create($userData)->id;
		
		$msmeData = [
				'id'=>uuid(),
				'user_id' => $userId,
				'udyam_no'=>$payload['udyam_no'],
				'entrepreneur_name'=> $payload['entrepreneur_name'],
				'enterprise_name'=> $payload['enterprise_name'],
				'organisation_type_id'=> $payload['organisation_type_id'],
				'gstin' => $payload['gstin'],
				'pan' => $payload['pan'],
				'gender' => $payload['gender'],
				'social_category' => $payload['social_category'],
				'specially_abled' => $payload['specially_abled'],
				'state_id' => $payload['state_id'],
				'district_id' => $payload['district_id'],
				'incorporation_date' => date('Y-m-d',strtotime($payload['incorporation_date'])),
				'nic_code' => $payload['nic_code'],
				'major_activity_id' => $payload['major_activity_id'],
				'total_emp' => $payload['total_emp'],
				'net_investment_plant_machinery' => $payload['net_investment_plant_machinery'],
				'turnover' => $payload['turnover'],
				'msme_classification_id' => $payload['msme_classification_id'],
				'dic_attached' => $payload['dic_attached'],
				'current_state_business_id' => $payload['current_state_business_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],
				'product_category_id' => $payload['product_category_id'],
				'physical_device_business_transactions' => $payload['physical_device_business_transactions'],
				'printer' => $payload['printer'],
				'catalogue_prodcut_details' => $payload['catalogue_prodcut_details'],
				'attending_ondc_awareness_workshop' => $payload['attending_ondc_awareness_workshop'],
				'products_geography' => $payload['products_geography'],
				'status'=> 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			
			];
		
		DB::table('msme_team_schemes')->insert($msmeData);
		
		
		$roleId = $commonService->getRoleId_by_slug('msme');
		//Add User Role and role permission
		    $updatedUserRoles = [];
                $updatedUserRoles =[
                        'user_id' => $userId,
                        'role_id' => $roleId->id,
                        'type' => GrantType::THROUGH_ROLE
                    ];
                               
                DB::table('user_roles')->insert($updatedUserRoles);

                $rolePermissions = DB::table('role_permissions')
                    ->select('permission_id')
                    ->where('role_id', $roleId->id)
                    ->get()
                    ->toArray();

                $userPermissions = DB::table('user_permissions')
                    ->select('permission_id')
                    ->where('user_id',$userId)
                    ->get()
                    ->toArray();

                $rolePermissions = $rolePermissions ? array_column($rolePermissions, 'permission_id') : [];
                $userPermissions = $userPermissions ? array_column($userPermissions, 'permission_id') : [];

                $mergedUserPermissions = array_unique(array_merge($rolePermissions, $userPermissions));

                $updatedUserPermissions = array_map(
                    fn ($permissionId) => ([
                        'user_id' => $userId,
                        'permission_id' => $permissionId,
                        'type' =>  GrantType::THROUGH_ROLE
                    ]), 
                    $mergedUserPermissions
                );

            
               DB::table('user_permissions')->insert($updatedUserPermissions);
				
			return true; 
			
		});
		
    }*/
}
