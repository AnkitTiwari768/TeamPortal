<?php 
declare(strict_types=1);
namespace App\Web\BNPRegistration;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class BnpService extends BaseService
{
    use DataTable;

   

    public function generateBNPId(): string
    {
        $lastNumber = DB::table('team_bnp_scheme')
            ->selectRaw('MAX(CAST(SUBSTRING(bnp_id, 4) AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNumber = (int)$lastNumber + 1;

        return 'BNP' . str_pad((string)$nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function store(array $payload, ?string $id = null): bool
    {
        
        return DB::transaction(function() use ($payload, $id) {
            $commonService = new CommonService();

            $bnpId = $this->generateBNPId();

            $userData = [
                'first_name' => $payload['authorized_person_name'] ,
                'mobile'=>      $payload['mobile'],                
                'email'=> 		 $payload['email'],
                'status'  =>      0 ,
                'is_role_mapped'=> 1
            ];
            if (! $id) 
            {
                $userData['id'] = uuid();
                $userData['username'] = $bnpId;
		        $userId = User::create($userData)->id;

            }else{
                //DB::table('users')->where('id', $id)->update($userData);
                User::where('id', $id)->update($userData);
                $userId = $id;
            }

		    $bnpData = [
				//'id'=>uuid(),
				'user_id' => $userId,
                'bnp_name' => $payload['authorized_person_name'],
				'organization_id'=>$payload['organization_id'],
				'organization_name'=> $payload['organization_name'],
				'bank_name' => $payload['bank_name'],
				'ifsc_code' => $payload['ifsc_code'],
				'account_no' => $payload['account_no'],
				'pan' => $payload['pan'],
				'gst_number' => $payload['gst_number'],
				'agreecheck' => $payload['agreecheck'],
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			
			];
            if (! $id) 
            {
                $bnpData['id'] = uuid();
                $bnpData['bnp_id'] = $bnpId;
                DB::table('team_bnp_scheme')->insert($bnpData);

            }else{
                
                DB::table('team_bnp_scheme')->where('user_id',$id)->update($bnpData);
            }
		

		$roleId = $commonService->getRoleId_by_slug('bnp');
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
}