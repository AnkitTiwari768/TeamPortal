<?php

declare(strict_types=1);

namespace App\Http\Api\V1\User;

readonly class UserDTO
{
    public function __construct(
        public ?string $id,
        public string $firstName,
        public ?string $middleName,
        public ?string $lastName,
        public string $email,
        public string $mobile,
        public ?string $landlineNumber,
        public ?int $isRoleMapped,
        public ?string $userRoles,
        public ?string $departmentId,
        public ?string $stateId,
        public ?string $password,
        public ?int $status,
    ) {}

    public static function create(array $payload, ?string $id = null): self
    {
        return new self(
            id: $id ?? null,
            firstName: $payload['first_name'],
            middleName: $payload['middle_name'] ?? null,
            lastName: $payload['last_name'] ?? null,
            email: $payload['email'],
            mobile: $payload['mobile'],
            landlineNumber: $payload['landline_number'] ?? null,
            isRoleMapped: $payload['is_role_mapped'] ?? null,
            userRoles: isset($payload['roles'])
                ? json_encode($payload['roles'])
                : null,
            departmentId: $payload['department_id'] ?? null,
            stateId: $payload['state_id'] ?? null,
            password: $payload['password'] ?? null,
            status: isset($payload['status']) ? (int) $payload['status'] : null,
        );
    }
}





  // public readonly string $userName,
   // public readonly string | null $alternateEmail,
    //public readonly string | null $alternateMobile,
	 //public readonly ?string $password = null
	 
// readonly class UserDTO 
// {
//     public function __construct(
//         public readonly string $id,
//         public readonly string $firstName,
//         public readonly string | null $middleName,
//         public readonly string | null $lastName,
//         public readonly string $email,
//         public readonly string $mobile,
//         public readonly string | null $landlineNumber,
//         public readonly int | null $isRoleMapped,
//         public readonly string  $userRoles,
//         public readonly string $departmentId,
//         public readonly string $stateId,
//         public readonly ?string $password = null,
//         public readonly int $status,
       
//     ) 
//     {}
// 	   // userName: $payload['username'],
// 	   // alternateEmail: $payload['alternate_email'] ?? null,
// 	    // alternateMobile: $payload['alternate_mobile'] ?? null,
// 		//password: (!$id) ? $payload['password'] : null,
//     public static function create(array $payload, ?string $id = null) 
//     {
//         return new self(
//             id: $id ?? createUUID(),
//             firstName: $payload['first_name'],
//             middleName: $payload['middle_name'] ?? null,
//             lastName: $payload['last_name'] ?? null,
//             email: $payload['email'],
//             mobile: $payload['mobile'],
//             landlineNumber: $payload['landline_number'] ?? null,
//             isRoleMapped: $payload['is_role_mapped'] ?? null,
//             userRoles: $payload['roles'] ?? [],
//             departmentId: $payload['department_id'],
//             stateId: $payload['state_id'],
//             password: (!$id) ? $payload['password'] : null,
//             status: (int) $payload['status'],
           
//         );
//     }
// }