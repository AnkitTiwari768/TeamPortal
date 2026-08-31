<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

readonly class CustomUserDto 
{
    public function __construct(
        public readonly string $firstName,        
        public readonly ?string $lastName,        
        public readonly string $email,        
        public readonly string $mobile,        
        public readonly ?array $role,
        public readonly ?string $stateId,
        public readonly ?string $zoneId,
        public readonly ?string $asiCircleId,
    )
    {
        
    }

    public static function fromRequest(array $request): self
    {
        return new self(
            firstName: $request['first_name'],
            lastName: $request['last_name'] ?? null,
            email: $request['email'],
            mobile: $request['mobile'],
            role: $request['role'] ?? [],
            stateId: $request['state_id'] ?? null,
            zoneId: $request['zone_id'] ?? null,
            asiCircleId: $request['asi_circle_id'] ?? null,
        );
    }

    public function getFullName(): string
    {
        return ucwords($this->firstName . ' ' . $this->lastName);
    }
}