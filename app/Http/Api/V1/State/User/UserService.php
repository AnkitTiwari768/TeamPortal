<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;

use App\Http\Services\ApiService;


class UserService extends ApiService
{
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUsers()
    {
		//dd('dfgdf');
        return $this->userRepository->getUsers();
    }

    public function save(UserDTO $userDTO, ?string $id = null) : bool
    {
        return $this->userRepository->save($userDTO, $id);
    }

    public function getDetails(?string $userId = null)
    {
        $CommonService = new \App\Http\Services\CommonService(); 
        return [
            'roles' => $userId ? array_map(
                fn($userRole) => ([
                    'id' => $userRole->id,
                    'text' => $userRole->name,
                ]),
                $this->userRepository->getUserRoles($userId)
            ) : [],
            'departments' => $CommonService->getDepartments(),
            'designations' => $CommonService->getDesignations(),
            'countries' => $CommonService->getCountries(),
			'states' => $CommonService->getStates(),
            'districts' => $CommonService->getDistricts(),
            'status' => $CommonService->getStatus()
        ];
    }

    public function getUser(string $id)
    { 
        return $this->userRepository->getUser($id);
    }
	
    public function getApplicantDetails(string $userId)
    {
        return \DB::table('applicants AS a')
            ->selectRaw('
                a.production_name AS production_company_name,
                a.production_type,a.representative_type,
                u.first_name,u.last_name,
                CONCAT_WS(" ", u.first_name, u.last_name) AS company_representative_name,
                u.email,
                u.mobile,
                u.alternate_mobile,
                a.country_id,
                a.state_id,
                a.city_id,
                a.address_first,
                a.address_second,
                a.postal_code,
                c.name AS country_name,
                s.name AS state_name,
                l.name AS city_name
            ')
            ->join('users AS u', 'a.user_id', '=', 'u.id')
            ->join('countries AS c', 'a.country_id', '=', 'c.id')
            ->join('states AS s', 'a.state_id', '=', 's.id')
            ->join('locations AS l', 'a.city_id', '=', 'l.id')
            ->where('u.id', $userId)
            ->first();
    } 
	 
}