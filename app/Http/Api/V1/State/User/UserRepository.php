<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use App\Contracts\GrantType;

use App\Core\CoreRepository;

use App\Http\Api\V1\User\Contracts\UserRepositoryInterface;

use App\Traits\DataTable;

class UserRepository extends CoreRepository implements UserRepositoryInterface
{
    use DataTable;

    protected $columns = [
        1 => 'full_name',
		2 => 'u.email',
		3 => 'u.mobile',
		5 => 'c.name',
        6 => 'u.status',
    ];

    public function getUsers()
    {
        $authId = AuthId();
        // $highestLevel = getUserHighestCategoryLevel($authId);
		[$limit, $order, $dir, $search, $page, $filters] = $this->getDataTableParams();

        $query = \DB::table('users AS u')
            ->selectRaw('
                u.id,
                CONCAT_WS(" ", u.first_name, u.middle_name, u.last_name) AS full_name,
                u.email,
                u.mobile,
                u.username,
                u.status,
                "test" AS state_name,
                "test" AS district_name,
                c.name AS country_name,
                (
                    SELECT JSON_ARRAYAGG(r.name) 
                    FROM user_roles AS ur
                    INNER JOIN roles AS r ON ur.role_id = r.id
                    WHERE ur.user_id = u.id 
                ) AS roles
            ')
            ->join('addresses AS a', 'a.id', '=', 'u.address_id')
            ->join('countries AS c', 'a.country_id', '=', 'c.id');
            // ->join('states AS s', 's.id', '=', 'a.state_id')
            // ->join('locations AS l', 'l.id', '=', 'a.district_id');

        // if (! hasRole(config('roles.administrator')) && config('settings.enable_hierarchical_list'))
        // {
        //     $query->join('categories AS c3', 'c3.id', '=', \DB::raw('( 
        //         select c5.id 
        //         from user_roles ur
        //         inner join roles r on ur.role_id = r.id
        //         inner join categories c5 on r.category_id = c5.id
        //         where ur.user_id = u.created_by
        //         order by c5.level asc
        //         limit 1)
        //     '));
        // }

        // if (! hasRole(config('roles.administrator'))) 
        // {
        //     $query->where(function($query) use ($authId, $highestLevel) {
        //         $query->where('u.created_by', $authId)
        //             ->orWhere('u.updated_by', $authId);

        //         if (config('settings.enable_hierarchical_list'))
        //         {
        //             $query->orWhereRaw('
        //                 (
        //                     SELECT MIN(c4.level) 
        //                     FROM user_roles AS ur2
        //                     INNER JOIN roles AS r2 ON ur2.role_id = r2.id
        //                     INNER JOIN categories AS c4 ON r2.category_id = c4.id
        //                     WHERE ur2.user_id = u.id 
        //                 ) > ?
        //             ', [$highestLevel]);  
        //         }
        //     });
        // }

        if (isset($search) && !empty($search) && $this->escape_special_characters($search)) 
        {
            $query->where(function($query) use ($search) {
                $query->whereRaw('CONCAT_WS(" ", u.first_name, u.middle_name, u.last_name) LIKE ?', ["%{$search}%"])
                    ->orWhere('u.email','LIKE',"%$search%") 
                    ->orWhere('u.mobile','LIKE',"%$search%") 
                    ->orWhere('c.name','LIKE',"%$search%") 
                    ->orWhereRaw('(
                        SELECT GROUP_CONCAT(r.name) 
                        FROM user_roles AS ur
                        INNER JOIN roles AS r ON ur.role_id = r.id
                        WHERE ur.user_id = u.id 
                    ) like ?', ["%". $search ."%"])  
                    ->orWhereRaw($this->datatable_status("u.status"). "LIKE '$search%'");    
            });
        }

        if (isset($filters['country_id']) && !empty($filters['country_id'])) {
            $query->where('c.id', $filters['country_id']);
        }

        if (isset($filters['state_id'])  && !empty($filters['state_id'])) {
            $query->where('s.id', $filters['state_id']);
        }

        if (isset($filters['district_id'])  && !empty($filters['district_id'])) {
            $query->where('l.id', $filters['district_id']);
        }

        // if (isset($filters['category_id'])) {
        //     $query->whereRaw('(
        //         SELECT GROUP_CONCAT(c2.id) 
        //         FROM user_roles AS ur
        //         INNER JOIN roles AS r ON ur.role_id = r.id
        //         INNER JOIN categories AS c2 ON r.category_id = c2.id
        //         WHERE ur.user_id = u.id 
        //     ) like ?', ["%". $filters['category_id'] ."%"]);
        // }

        
        if (isset($filters['role_id']) && !empty($filters['role_id'])) {
            $query->whereRaw('(
                SELECT GROUP_CONCAT(r.id) 
                FROM user_roles AS ur
                INNER JOIN roles AS r ON ur.role_id = r.id
                WHERE ur.user_id = u.id 
            ) like ?', ["%". $filters['role_id'] ."%"]);
        }

        if (isset($filters['status']) && !empty($filter['status'])) {
            $query->where('u.status', $filters['status']);
        }

         if (isset($filters['status']) && $filters['status'] === '0') {
            $query->where('u.status', $filters['status']);
        }

        $query->orderBy($order, $dir); 

        // dd($query->toSql()); die;

        if (isset($page) && !empty($page)) 
        {
            return $this->getDataTableResult(
                UserResource::collection($query->paginate($limit))
            );
        }

        return UserResource::collection($query->get());
    }

    public function save(UserDTO $userDTO, ?string $id = null) : bool
    {
        return \DB::transaction(function() use ($userDTO, $id) {
            
            $user = ($id) ? User::findOrFail($id) : new User();
            $user->id = $userDTO->id;
            $user->first_name = $userDTO->firstName;
            $user->middle_name = $userDTO->middleName;
            $user->last_name = $userDTO->lastName;
           // $user->username = $userDTO->userName;
            $user->email = $userDTO->email;
           // $user->alternate_email = $userDTO->alternateEmail;
            $user->mobile = $userDTO->mobile;
          //  $user->alternate_mobile = $userDTO->alternateMobile;
            $user->department_id = $userDTO->departmentId;
            $user->designation_id = $userDTO->designationId;
            $user->zone_id = $userDTO->zoneId;
            $user->status = $userDTO->status;
            $user->is_role_mapped = $userDTO->isRoleMapped;

            if (! $id) 
            {
                $email = $this->getVerificationDetails($userDTO->email);
                //$alternateEmail = ($userDTO->alternateEmail)?$this->getVerificationDetails($userDTO->alternateEmail): $userDTO->alternateEmail;
                $mobile = $this->getVerificationDetails($userDTO->mobile);
                //$alternateMobile = ($userDTO->alternateMobile)?$this->getVerificationDetails($userDTO->alternateMobile):$userDTO->alternateMobile;

                // if (! $email?->is_verified) 
                //     throw new \Exception("Email is not verified");

                // if (! $alternateEmail?->is_verified) 
                //     throw new \Exception("Alternate email is not verified");
    
                // if (! $mobile?->is_verified) 
                //     throw new \Exception("Mobile is not verified");

                // if (! $alternateMobile?->is_verified) 
                //     throw new \Exception("Alternate mobile is not verified");

                $addressId = createUUID();
                
                \DB::table('addresses')->insert([
                    'id' => $addressId,
                    'address' => $userDTO->address,
                    'country_id' => $userDTO->countryId,
                    'state_id' => $userDTO->stateId,
                    'district_id' => $userDTO->districtId,
                    'postal_code' => $userDTO->postalCode,
                    'created_at' => currentDateTime()
                ]);

                $user->address_id = $addressId;
                //$user->password = $userDTO->password;
                $user->email_verified = $email?->is_verified;
                $user->email_verified_at = $email?->verified_at;
                //$user->alternate_email_verified = $alternateEmail?->is_verified;
                //$user->alternate_email_verified_at = $alternateEmail?->verified_at;
                $user->mobile_verified_at = $mobile?->is_verified;
                $user->mobile_verified_at = $mobile?->verified_at;
                //$user->alternate_mobile_verified = $alternateMobile?->is_verified;
                //$user->alternate_mobile_verified_at = $alternateMobile?->verified_at;
                $user->created_at = currentDateTime();
                $user->created_by = AuthId();
            }
            else 
            {
                $address = \DB::table('addresses')
                    ->where('id', $user->address_id)
                    ->first();
                    
                if ($address) 
                {
                    \DB::table('addresses')
                        ->where('id', $user->address_id)
                        ->update([
                            'address' => $userDTO->address,
                            'country_id' => $userDTO->countryId,
                            'state_id' => $userDTO->stateId,
                            'district_id' => $userDTO->districtId,
                            'postal_code' => $userDTO->postalCode,
                            'updated_at' => currentDateTime(),
                        ]);
                }
                    
                $user->updated_at = currentDateTime();
                $user->updated_by = AuthId();
            }

            $user->save();

            if ($userDTO->isRoleMapped && $userDTO->userRoles) 
            {
                $updatedUserRoles = [];

                $updatedUserRoles = array_map(
                    fn ($roleId) => ([
                        'user_id' => $userDTO->id,
                        'role_id' => $roleId,
                        'type' => GrantType::THROUGH_ROLE
                    ]), 
                    $userDTO->userRoles
                );

                \DB::table('user_roles')
                    ->where('user_id', $id)
                    ->delete();

                \DB::table('user_roles')
                    ->insert($updatedUserRoles);

                $rolePermissions = \DB::table('role_permissions')
                    ->select('permission_id')
                    ->whereIn('role_id', $userDTO->userRoles)
                    ->get()
                    ->toArray();

                $userPermissions = \DB::table('user_permissions')
                    ->select('permission_id')
                    ->where('user_id', $userDTO->id)
                    ->get()
                    ->toArray();

                $rolePermissions = $rolePermissions ? array_column($rolePermissions, 'permission_id') : [];
                $userPermissions = $userPermissions ? array_column($userPermissions, 'permission_id') : [];

                $mergedUserPermissions = array_unique(array_merge($rolePermissions, $userPermissions));

                $updatedUserPermissions = array_map(
                    fn ($permissionId) => ([
                        'user_id' => $userDTO->id,
                        'permission_id' => $permissionId,
                        'type' =>  GrantType::THROUGH_ROLE
                    ]), 
                    $mergedUserPermissions
                );

                \DB::table('user_permissions')
                    ->where('user_id', $userDTO->id)
                    //->where('type', GrantType::THROUGH_ROLE)
                    ->delete();

                \DB::table('user_permissions')
                    ->insert($updatedUserPermissions);
            }

            return true;

        });
    }

    public function getVerificationDetails(string $key)
    {
        return \DB::table('verifications')
            ->select('verified_at', 'is_verified')
            ->where('verification_key', $key)
            ->first();
    }

    public function getUser(string $id)
    {
        return  \DB::table('users AS u')
            ->selectRaw('
                u.id,
                u.first_name,
                u.middle_name,
                u.last_name,
                u.username,
                u.email,
                u.email_verified,
                u.alternate_email,
                u.alternate_email_verified,
                u.mobile,
                u.mobile_verified,
                u.alternate_mobile,
                u.alternate_mobile_verified,
                u.status,
                u.department_id,
                u.designation_id,
                u.zone_id,
                u.photo_file_upload_id,
                u.profile_picture,
                a.country_id,
                a.state_id,
                a.district_id,
                d.name AS department_name,
                ds.name AS designation_name,
                c.name AS country_name,
                s.name AS state_name,
                l.name AS district_name,
                a.address,
                a.postal_code,
                (
                    SELECT JSON_ARRAYAGG(r.name)
                    FROM roles AS r
                    INNER JOIN user_roles AS ur ON ur.role_id = r.id
                    WHERE ur.user_id = u.id 
                ) AS roles
            ')
            ->join('departments AS d', 'd.id', '=', 'u.department_id')
            ->join('designations AS ds', 'ds.id', '=', 'u.designation_id')
            ->join('addresses AS a', 'a.id', '=', 'u.address_id')
            ->join('countries AS c', 'c.id', '=', 'a.country_id')
            ->join('states AS s', 's.id', '=', 'a.state_id')
            ->join('locations AS l', 'l.id', '=', 'a.district_id')
            ->where('u.id', $id)  
            ->first();        
	//var_export($query->toSql()); die;	
	
    }

    public function getUserRoles(string $userId)
    {
        return \DB::table('user_roles AS ur')
            ->select('r.id', 'r.name')
            ->join('roles AS r', 'r.id', '=', 'ur.role_id')
            ->where('ur.user_id', $userId)
            ->get()
            ->toArray();
    }
	
	
    public function getUserById(string $id) 
    {
        return User::select('email', 'mobile')
                ->where('id', $id)
                ->first();
    }
}