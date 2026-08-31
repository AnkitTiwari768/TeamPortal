<?php 
declare(strict_types=1);
namespace App\Web\SNPRegistration;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Traits\{HasAttribute,HasFileUpload};
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class SnpService extends BaseService
{
    use DataTable,HasAttribute,HasFileUpload;

    public function getDropdownList()
    {
        $commonService = new CommonService();
        return [
		    'domain'=>$this->listOf('domain'),
            'ondc_types'=>$this->listOf('types-of-transactions-preferred-on-ondc'),
            'production_status'=>$this->listOf('np-registration-production-status'),
            'subdomains' => $commonService->getDropdownNewList('sub_domains','status','ASC','name',array('id','name')),
            'states' => $commonService->getDropdownNewList('states','status','ASC','name',array('id','name')),
            'status' => $commonService->getStatus(),
            'snp_commercial' => $commonService->snp_commercial()
        ];
    }

    public function getsubdomains($domains)
    {
        return \DB::table('sub_domains')->select('id','name')->whereIn('domain_id',$domains)->get();
    }

    public function deleteDocuments($payload){

        if($payload['document_type']=='authorized_certificate'){ 
            $path="app/".config('upload.authorized_certificate_document_file_path');  
        }
        if($payload['document_type']=='cancelled_cheque'){
            $path="app/".config('upload.cancelled_cheque_document_file_path');  
        }
        if($payload['document_type']=='commercial_model'){
            $path="app/".config('upload.commercial_model_document_file_path');  
        }
        if($payload['document_type']=='description'){
            $path="app/".config('upload.description_document_file_path');  
        }
        $this->deleteFile($path,$payload['id']);
        return true;
    }

    public function generateSNPId(): string
    {
        $lastNumber = DB::table('team_snp_scheme')
            ->selectRaw('MAX(CAST(SUBSTRING(snp_id, 4) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = (int)$lastNumber + 1;

        return 'SNP' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $payload, ?string $id = null): bool
    {
        
        return DB::transaction(function() use ($payload, $id) {
            $commonService = new CommonService();

            $snpId = $this->generateSNPId();
            //dd($snpId);
            //$password = $this->generateStrongPassword(8);
            //dd($password);
            $userData = [
                'first_name' => $payload['authorized_person_name'] ,
                'mobile'=>      $payload['mobile'],
                'alternate_mobile'=>      $payload['contanct_no']??null,
                
                'email'=> 		 $payload['email'],
                'alternate_email'=> $payload['alternate_email']??null,
                //'password'=>     $password,
                'status'  =>      0 ,
                'is_role_mapped'=> 1
            ];
            if (! $id) 
            {
                $userData['id'] = uuid();
                $userData['username'] = $snpId;
		        $userId = User::create($userData)->id;

            }else{
                //DB::table('users')->where('id', $id)->update($userData);
                User::where('id', $id)->update($userData);
                $userId = $id;
            }

		    $snpData = [
				//'id'=>uuid(),
				'user_id' => $userId,
                'snp_name' => $payload['authorized_person_name'],
				'organization_id'=>$payload['organization_id'],
				'organization_name'=> $payload['organization_name'],
				'brand_name'=> $payload['brand_name'] ?? null,
				'designation'=> $payload['designation'],
                'authorized_certificate_document' =>  $payload['authorized_certificate_document'] ?? null,
                'authorized_certificate_document_original_name' => isset($payload['authorized_certificate_document']) 
                    ? self::originalName($payload['authorized_certificate_document']) : null,
				'domain' => json_encode($payload['domain']), //implode(',', $payload['domain']),
				'sub_domain' => json_encode($payload['sub_domain']),//implode(',', $payload['sub_domain']),
				'transaction_type' => json_encode($payload['transaction_type']), //implode(',', $payload['transaction_type']),
				'state_id' => json_encode($payload['state_id']), //implode(',', $payload['state_id']),
				'bank_name' => $payload['bank_name'],
				'ifsc_code' => $payload['ifsc_code'],
				'account_no' => $payload['account_no'],
				'pan' => $payload['pan'],
				'gst_number' => $payload['gst_number'],
				'cancelled_cheque_document' =>  $payload['cancelled_cheque_document'] ?? null,
                'cancelled_cheque_document_original_name' => isset($payload['cancelled_cheque_document']) 
                    ? self::originalName($payload['cancelled_cheque_document']) : null,
                'commercial_model' => $payload['commercial'] ?? null,
                'commercial_model_document' =>  $payload['commercial_model_document'] ?? null,
                'commercial_model_document_original_name' => isset($payload['commercial_model_document']) 
                        ? self::originalName($payload['commercial_model_document']) : null,
				'live_seller' => $payload['live_seller'],
                'date_of_going_live_on_ondc' => date('Y-m-d',strtotime($payload['date_of_going_live_on_ondc'])),
				'no_of_transactions_done' => $payload['no_of_transactions_done'],
				'short_description' => $payload['short_description']??null,
				'description_document' =>  $payload['description_document'] ?? null,
                'description_document_original_name' => isset($payload['description_document']) 
                        ? self::originalName($payload['description_document']) : null,
				'agreecheck' => $payload['agreecheck'],
				'status' => $payload['form_status'],
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			
			];
            if (! $id) 
            {
                $snpData['id'] = uuid();
                $snpData['snp_id'] = $snpId;
                DB::table('team_snp_scheme')->insert($snpData);

            }else{
                
                DB::table('team_snp_scheme')->where('user_id',$id)->update($snpData);
            }
		

		$roleId = $commonService->getRoleId_by_slug('snp');
		//Add User Role and role permission
		    $updatedUserRoles = [];
                $updatedUserRoles =[
                        'user_id' => $userId,
                        'role_id' => $roleId->id,
                        'type' => GrantType::THROUGH_ROLE
                    ];

                \DB::table('user_roles')
                    ->where('user_id', $userId)
                    ->delete();
                               
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
                
                \DB::table('user_permissions')
                    ->where('user_id', $userId)
                    //->where('type', GrantType::THROUGH_ROLE)
                    ->delete();

            
                DB::table('user_permissions')->insert($updatedUserPermissions);


                $email = $payload['email'];
                $templateData['name'] = $payload['authorized_person_name'];

                if (! $id) 
                {
                    $subject = "Registration Successfull";
                    $templateData['status'] = 'new';

                }else{
                    
                    $subject = "Registration Update Successfull";
                    $templateData['status'] = 'update';
                }

                $body = view('emails.snp_registration_mail',$templateData)->render();
                $to = $email;
                
                app(PHPMailerService::class)->sendEmail($to, $subject, $body);
				
			return true; 
			
		});
		
    }

    public function getRoles(){
        $role_slug = ['snp','bnp','lsp'];
        return DB::table('roles')->select('id','name','slug')->whereIn('slug',$role_slug)->get();
    }
}