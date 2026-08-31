<?php
namespace App\Web\MseBulkRegistration;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use DateTime;


class MseBulkImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {		
	    dd($rows);
		
		DB::transaction(function () use ($rows) {
			foreach ($rows as $payload) {
				
				$udetails=$this->getUdyamDetails($payload['udyam_no'],$payload['mobile']);
				
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
						'total_emp' => $udetails['BasicDetail']['TotalEmp'],
						'state_id' => $this->getStateId('states',$udetails['BasicDetail']['LG_ST_Code']),
						'district_id' =>$this->getStateId('locations',$udetails['BasicDetail']['LG_DT_Code']),
						'enterprise_details' => json_encode($udetails['BasicDetail']['EnterpriseDetail']),
						'activity_details' => json_encode($udetails['ActivityDetail']),
						
						'gstin' => strtolower($payload['gstin']) === 'yes' ? 1 : 0,
						'gstin_no' => $payload['gstin_no'],
						
						'pan' => strtolower($payload['pan']) === 'yes' ? 1 : 0,
						'pan_no' => $payload['pan_no'],
						
						'nic_code' => $payload['nic_code'],
						
						'specially_abled' => strtolower($payload['specially_abled']) === 'yes' ? 1 : 0,
						
						'net_investment_plant_machinery' => $payload['net_investment_plant_machinery'],
						'turnover' => $payload['turnover'],
						
						'dic_attached' => $payload['dic_attached']??null,
						
						'current_state_business_id' => $this->getCurrentBuisinessTypes('attribute_values',slugify($payload['current_state_business_id'])),
						
						'ondc_transaction_type_id' => $this->getTransactionTypes('attribute_values',slugify($payload['ondc_transaction_type_id'])),
						
						'product_category_id' => $this->getSubdomainTypes('sub_domains',slugify($payload['product_category_id'])),
						
						'select_snp' => strtolower($payload['select_snp']) === 'yes' ? 1 : 0,
						
						'physical_device_business_transactions' =>strtolower($payload['physical_device_business_transactions']) === 'yes' ? 1 : 0,
						
						'printer' => strtolower($payload['printer']) === 'yes' ? 1 : 0,
						
						'catalogue_prodcut_details' => strtolower($payload['catalogue_prodcut_details']) === 'yes' ? 1 : 0,
						
						'attending_ondc_awareness_workshop' => strtolower($payload['attending_ondc_awareness_workshop']) === 'yes' ? 1 : 0,
						
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
					
				}
			
			});
           
    }
	
	
	public function getCurrentBuisinessTypes($table,$state_code){
		return DB::table($table)->where('code',$state_code)->first()->id;
	}
	
	public function getTransactionTypes($table,$code){
		return DB::table($table)->where('code',$code)->first()->id;
	}
	
	
	
	public function getSubdomainTypes($table,$code){
		$sub = explode(",", $code);
		$ids = DB::table($table)->whereIn('code', $sub)->pluck('id'); 

		return json_encode($ids);
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
		
		
}
