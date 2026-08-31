<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\User;
  // public readonly string $userName,
   // public readonly string | null $alternateEmail,
    //public readonly string | null $alternateMobile,
	 //public readonly ?string $password = null
	 
readonly class UserDTO 
{
    public function __construct(
        public readonly string $id,
        public readonly string $firstName,
        public readonly string | null $middleName,
        public readonly string | null $lastName,
        public readonly string $email,
        public readonly string $mobile,
        public readonly string | null $landlineNumber,
        public readonly int | null $isRoleMapped,
        public readonly array  $userRoles,
        public readonly string $departmentId,
        public readonly string $designationId,
        public readonly string | null $zoneId,
        public readonly string $countryId,
        public readonly string $stateId,
        public readonly string $districtId,
        public readonly string $address,
        public readonly string $postalCode,
        public readonly int $status,
       
    ) 
    {}
	   // userName: $payload['username'],
	   // alternateEmail: $payload['alternate_email'] ?? null,
	    // alternateMobile: $payload['alternate_mobile'] ?? null,
		//password: (!$id) ? $payload['password'] : null,
    public static function create(array $payload, ?string $id = null) 
    {
        return new self(
            id: $id ?? createUUID(),
            firstName: $payload['first_name'],
            middleName: $payload['middle_name'] ?? null,
            lastName: $payload['last_name'] ?? null,
            email: $payload['email'],
            mobile: $payload['mobile'],
            landlineNumber: $payload['landline_number'] ?? null,
            isRoleMapped: $payload['is_role_mapped'] ?? null,
            userRoles: $payload['roles'] ?? [],
            departmentId: $payload['department_id'],
            designationId: $payload['designation_id'],
            zoneId: $payload['zone_id'] ?? null,
            countryId: $payload['country_id'],
            stateId: $payload['state_id'],
            districtId: $payload['district_id'],
            address: $payload['address'],
            postalCode: $payload['postal_code'],
            status: (int) $payload['status'],
           
        );
    }
}