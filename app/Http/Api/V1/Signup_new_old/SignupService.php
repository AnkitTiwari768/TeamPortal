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
use DateTime;



class SignupService 
{
    use DataTable,HasAttribute;
	

    public function store(array $payload, ?string $roleId = null): bool
    {
	
	return DB::transaction(function() use ($payload) {
		
		$uuid=uuid();
		$userId=uuid();
		$msmeData = [
				'id'=>$uuid,
				'user_id'=>$userId,
				//'team_id'=>'TEAM'.rand(10000, 99999),
				'team_id' =>  getNextTeamId() ?: null,
				'entrepreneur_name'=> $payload['entrepreneur_name'],
				'enterprise_name'=> $payload['enterprise_name'],
				'udyam_no'=>$payload['udyam_no']??null,
				'mobile' => $payload['mobile'],
				'email' => $payload['email'],
				'product_category_id' => json_encode($payload['product_category_id']),
				'product_details' => $payload['product_details'],
				'state_id' => $payload['state_id'],
				'ondc_transaction_type_id' => $payload['ondc_transaction_type_id'],	
				'select_snp' => $payload['select_snp'],				
				'status'=> 0,
				'agree' => 1,
				'is_msme_registration'=> 1,
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			];
	
			DB::table('team_msme_schemes')->insert($msmeData);
			$user_mapping=[
				'id'=>$userId,
				'username'=>$payload['email'],
				'mobile'=> $payload['mobile'],
				'email'=> $payload['email'],
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
		
    }

	
	public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
		 'states' => $commonService->getDropdownNewList('states','status','ASC','name',array('id','name')),
		 'current_state_business'=>$this->listOf('current-state-of-your-business'),
		 'ondc_types'=>$this->listOf('types-of-transactions-preferred-on-ondc'),
		 'sub_domains' => $commonService->getDropdownNewList('sub_domains','status','ASC','name',array('id','name')),

		 'yesno' => $commonService->getYesNoStatus(),
         'status' => $commonService->getStatus()
        ];
    }

	public function getUdyamDetails($udyam_no,$mobile){
		$response= Http::get("https://udyogaadhaar.gov.in/sv/Udyam_NsicB2BService.svc/GetUdyam/$udyam_no,$mobile,b2bmrt-VGVzdEBoeXc2MA==");
		
		if($response->successful()) {
			$xml = $response->body();
			$xmlObject = simplexml_load_string($xml);
			$json = json_encode($xmlObject);
			//echo "<pre/>";print_r(json_decode($json, true));exit;
			return json_decode($json, true);
		}else{
			return response()->json([
				'error' => 'Failed to retrieve data',
				'status' => $response->status()
			]);
		}
		
		
	}

	public function update(array $payload, ?string $id = null): bool
    {
	
	$udetails=$this->getUdyamDetails($payload['udyam_no'],$payload['mobile']);
	//echo "<pre/>";print_r($udetails);exit;

	return DB::transaction(function() use ($payload,$udetails,$id) {
		
		$msmeData = [
				'udyam_no'=>$payload['udyam_no'],
				'state_id' => $this->getStateId('states',$udetails['BasicDetail']['LG_ST_Code']),
				'district_id' =>$this->getStateId('locations',$udetails['BasicDetail']['LG_DT_Code']),
				'organisation_type'=> $udetails['BasicDetail']['OrganisationType'],
				'gender' => $udetails['BasicDetail']['Gender'],
				'social_category' => $udetails['BasicDetail']['SocialCategory'],
				'major_activity' => $udetails['BasicDetail']['MajorActivity'],
				'msme_classification' => $udetails['BasicDetail']['EnterpriseType'],
				'gstin_no' => $payload['gstin_no'],
				'pan_no' => $payload['pan_no'],
				'status'=> 1,
				'is_msme_registration'=> 2,
				'updated_at' => currentDateTime()
			];
	
			$result = DB::table('team_msme_schemes')->where('id',$id)
				->where('mobile',$payload['mobile'])
				//->where('mobile','9876546789')
				->update($msmeData);
			
			//mapping with snp and msme
			if($result)
			{
				$snpId = $this->getSnpDetails(AuthId());
				//check if msme id exists in mapping table or not. if exists update otherwise insert
				DB::table('team_snpmsme_mapping')->updateOrInsert(
					['msme_id' => $id], // condition
					[
						'id' => uuid(),
						'snp_id' => $snpId,
						'created_at' => currentDateTime(),
						'updated_at' => currentDateTime()
					]
				);
				
				//DB::table('team_snpmsme_mapping')->insert($snp_mapping);
				return true; 
			}else{
				return false; 
			}
			
			
			
		});
		
    }
	
	
	public function getSelectSnpDetails($state_id,$transaction_type,$sub_domain){
		$query = DB::table('team_snp_scheme')->select('id','snp_name','commercial_model','commercial_model_document','live_seller','date_of_going_live_on_ondc','no_of_transactions_done','short_description','description_document','organization_name')
		    ->where('status',1)
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

	public function getSnpDetails($id){
		return DB::table('team_snp_scheme')
        ->where('user_id', $id)
        ->value('id'); 
	}

	public function getStateId($table,$state_code){
		return DB::table($table)->where('code',$state_code)->first()->id;
	}
	
}