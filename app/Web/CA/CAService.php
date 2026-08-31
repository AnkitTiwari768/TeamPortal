<?php 
declare(strict_types=1);
namespace App\Web\CA;
use App\Traits\DataTable;
use App\Core\BaseService;
use App\Http\Services\CommonService;
use App\Models\User;
use App\Contracts\GrantType;
use DB;
use Illuminate\Support\Str;
use App\Services\PHPMailerService;

class CAService extends BaseService
{
    use DataTable;

    function generateStrongPassword($length = 8): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()-_=+<>?';

        $all = $upper . $lower . $numbers . $symbols;

        // Ensure the password contains at least one of each
        $password = $upper[random_int(0, strlen($upper) - 1)] .
                    $lower[random_int(0, strlen($lower) - 1)] .
                    $numbers[random_int(0, strlen($numbers) - 1)] .
                    $symbols[random_int(0, strlen($symbols) - 1)];

        // Fill the remaining characters randomly
        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        // Shuffle to avoid predictable pattern
        return str_shuffle($password);
    }

    public function store(array $payload, ?string $id = null): bool
    {
        
        return DB::transaction(function() use ($payload, $id) {
            $commonService = new CommonService();
            //dd($payload);
           
            $password = $this->generateStrongPassword(8);
            
            $userData = [
                'first_name' => $payload['first_name'] ,
                'middle_name' => $payload['middle_name'] ?? null,
                'last_name' => $payload['last_name'] ,
                'mobile'=>      $payload['mobile'],
                'email'=> 		 $payload['email'],
                'username'=> 		 $payload['email'],
                
                'status'  =>      1,
                'is_role_mapped'=> 1
            ];
            if (! $id) 
            {

                $userData['id'] = uuid();
                $userData['password'] =  $payload['password'];//$password,
		        $userId = User::create($userData)->id;

            }else{
                User::where('id', $id)->update($userData);
                $userId = $id;
            }

		    $snpCaData = [
				'id'=>uuid(),
				'ca_user_id' => $userId,
                'snp_user_id' => AuthId(),
				'created_at' => currentDateTime(),
				'updated_at' => currentDateTime()
			
			];

            DB::table('team_snpca_mapping')->where('snp_user_id',AuthId())->delete();
            DB::table('team_snpca_mapping')->insert($snpCaData);


		$roleId = $commonService->getRoleId_by_slug('ca');
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

                
                $permissioinsToBeAssigned = [];
                
                if (hasRole('snp')) {
                    $permissioinsToBeAssigned = [
                        'claim-view',
                        'claim-revert',
                        'claim-forward',
                        'account-management-claim-view',
                        'account-management-revert',
                        'account-management-forward',
                        'packaging-claim-view'
                    ];
                }

                if (hasRole('lsp')) {
                    $permissioinsToBeAssigned = [
                        'logistic-transportation-claim-view',
                    ];
                }

                if (hasRole('bnp')) {
                    $permissioinsToBeAssigned = [
                        'demand-generation-view',
                    ];
                }

                $permissionIds = DB::table('permissions')
                    ->whereIn('slug', $permissioinsToBeAssigned)
                    ->pluck('id')
                    ->toArray();
                
                $userPermissions = [];

                if ($permissionIds) {
                    foreach ($permissionIds as $permissionId) 
                    {
                        $userPermissions[] = [
                            'user_id' => $userId,
                            'permission_id' => $permissionId,
                            'type' => GrantType::THROUGH_ROLE
                        ];
                    }
                }

                if ($userPermissions) {
                    DB::table('user_permissions')
                        ->where('user_id', $userId)
                        ->delete();
                    DB::table('user_permissions')->insert($userPermissions);
                }

                /*$email = $payload['email'];
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
                
                app(PHPMailerService::class)->sendEmail($to, $subject, $body);*/
				
			return true; 
			
		});
		
    }
}