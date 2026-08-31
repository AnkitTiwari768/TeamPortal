<?php 

declare(strict_types=1);
namespace App\Http\Api\V1\UserProfile;
use App\Http\Services\ApiService; 
use App\Models\User as Model;
use App\Http\Api\V1\User\UserResource; 
use App\Models\PasswordHistory;
use App\Enums\ProductionType;
use App\Traits\HasFileUpload;

class UserProfileService extends ApiService 
{
    use HasFileUpload;
    public function save(array $payload) 
    {
        $userId = AuthId();

        $userProfileData = [
            'first_name' => $payload['first_name'],
            'middle_name' => $payload['middle_name'] ?? null,
            'last_name' => $payload['last_name'],
            //'email' => $payload['email'],
            'mobile' => $payload['mobile'],
            'alternate_mobile' => $payload['alternate_mobile']
        ];

        return \DB::table('users')
            ->where('id', $userId)
            ->update($userProfileData);
    }

    public function findById(string $id)
    { 
        $query = Model::select('users.*','addresses.*',
        'departments.name as department_name', 'designations.name as designation_name',
        'countries.name as country_name' ,'states.name as state_name','locations.name as district_name');  
        $query->join('departments', 'departments.id', '=', 'users.department_id');
        $query->join('designations', 'designations.id', '=', 'users.designation_id');
        $query->join('addresses', 'addresses.id', '=', 'users.address_id');
        $query->join('countries', 'countries.id', '=', 'addresses.country_id');
        $query->join('states', 'states.id', '=', 'addresses.state_id');
        $query->join('locations', 'locations.id', '=', 'addresses.district_id'); 
        $query->whereRaw('users.id = ?', [$id]);   
        return UserResource::collection($query->get())->first();
         
    }

    

    public function updatePhoto($payload, $id)
    {
        return \DB::transaction(function() use ($payload, $id) {  
            $user = Model::findOrFail($id);
            if($payload['photo_file_upload_id']!=$user->photo_file_upload_id)
            {
                if($user->profile_picture){
                    $location = config('constant.user_image_file_path'); 
                    unlink(storage_path('app/'.$location. '/'. $user->profile_picture));
                } 
            }
            
            if ($payload['photo_file_upload_id']) {
                $result = \DB::table('file_uploads')->where('id', $payload['photo_file_upload_id'])->first();
                $payload['profile_picture'] = $result ? $result->file_system_name : null;
            }
            return $user->fill($payload)->save();
        });
    }

    public function updatePassword($payload, $id)
	{
		return \DB::transaction(function() use ($payload, $id) {  
           // dd($payload);
			$user = Model::findOrFail($id);
        	$user->fill($payload)->save();
          
            $passwordhistory= PasswordHistory::create([
                'user_id' => $user->id,
                'password' => $user->password
            ]);
		 	
			// Mail::send('emails.change-password', function($message) use ($user) {
            //     $message->to($user->email)
            //         ->subject('Password Changed Successfully')
            //         ->from('no-reply@mom.gov.in', 'Mines');
            // });           
		});
	}

    public function updateApplicant($payload,$id){ 

         return \DB::transaction(function() use ($payload,$id) {
            
            if(isset($payload['isProfile']) && $payload['isProfile'] == true){
                $userData = [
                    'first_name' => $payload['first_name'] ?? null,
                    'middle_name'=> $payload['middle_name']?? null, 
                    'last_name' =>  $payload['last_name']?? null,
                    'mobile'=>      $payload['mobile']?? null,
                    'username'=> $payload['email']?? null,
                    'email'=> $payload['email'] ?? $payload['email']?? null,			
                ];
                \DB::table('users')
                    ->where('id', $id)
                    ->update($userData);
            }
            if(isset($payload['isSnp']) && $payload['isSnp'] == true ){
                $userData = [
                   
                    'mobile'=> $payload['mobile'],
                    'alternate_mobile'=> $payload['alternate_mobile']??null,
                    'email'=> $payload['email'],
                    'alternate_email'=> $payload['alternate_email']??null,
                    'status'  =>0,
                    'updated_by'=> $id, 
                    'updated_at' => currentDateTime()
                ];
                \DB::table('users')
                   ->where('id', $id)
                    ->update($userData);
                $snpData = [
                    'domain' => json_encode($payload['domain']), //implode(',', $payload['domain']),
                    'sub_domain' => json_encode($payload['sub_domain']),//implode(',', $payload['sub_domain']),
                    'transaction_type' => json_encode($payload['transaction_type']), //implode(',', $payload['transaction_type']),
                    'state_id' => json_encode($payload['state_id']), //implode(',', $payload['state_id']),
                    'bank_name' => $payload['bank_name'],
                    'ifsc_code' => $payload['ifsc_code'],
                    'account_no' => $payload['account_no'],
                    'pan' => $payload['pan'],
                    'gst_number' => $payload['gst_number'],                   
                    'live_seller' => $payload['live_seller'],                   
                    'no_of_transactions_done' => $payload['no_of_transactions_done'],
                    'updated_at' => currentDateTime()
                
                ];
                if (isset($payload['cancelled_cheque_document'])) {
                    $snpData['cancelled_cheque_document'] = $payload['cancelled_cheque_document'];
                    $snpData['cancelled_cheque_document_original_name'] = self::originalName($payload['cancelled_cheque_document']);
                }
                
                
                \DB::table('team_snp_scheme')
                    ->where('user_id', $id)
                    ->update($snpData);
            }
            if(isset($payload['isBnp']) && $payload['isBnp'] == true ){
                $userData = [
                   
                    'mobile'=> $payload['mobile'],
                    'email'=> $payload['email'],
                    'status'  => 0,
                    'updated_by'=> $id, 
                    'updated_at' => currentDateTime()
                ];
                \DB::table('users')
                   ->where('id', $id)
                    ->update($userData);
                $snpData = [
                    'bank_name' => $payload['bank_name'],
                    'ifsc_code' => $payload['ifsc_code'],
                    'account_no' => $payload['account_no'],
                    'pan' => $payload['pan'],
                    'gst_number' => $payload['gst_number'],
                    'updated_at' => currentDateTime()
                
                ];

                \DB::table('team_bnp_scheme')
                    ->where('user_id', $id)
                    ->update($snpData);
            }
        
        });
    }
}