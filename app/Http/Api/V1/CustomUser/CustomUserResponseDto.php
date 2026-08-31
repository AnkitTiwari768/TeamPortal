<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

readonly class CustomUserResponseDto 
{
    public function __construct(
        public readonly string $id,        
        public readonly string $first_name,        
        public readonly ?string $last_name,        
        public readonly string $email,        
        public readonly string $mobile,        
        public readonly ?array $role,
        public readonly ?string $state_id,
        public readonly ?string $zone_id,
    )
    {}

    public static function fromModel(mixed $userData): self 
    {
        return new self(
            id: $userData->id,
            first_name: $userData->first_name,
            last_name: $userData->last_name ?? null,
            email: $userData->email,
            mobile: $userData->mobile,
            role: $userData->roles ? json_decode($userData->roles) : [],
            state_id: $userData->state_id ?? null,
            zone_id: $userData->zone_id ?? null,
        );
    }
}
        
    