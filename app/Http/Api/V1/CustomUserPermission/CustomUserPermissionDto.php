<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUserPermission;

readonly class CustomUserPermissionDto 
{
    public function __construct(
        public readonly string $userId,
        public readonly array $permissions
    )
    {
        
    }

    public static function fromRequest(array $request): self
    {
        return new self(
            userId: $request['user_id'],
            permissions: $request['permissions']
        );
    }
}