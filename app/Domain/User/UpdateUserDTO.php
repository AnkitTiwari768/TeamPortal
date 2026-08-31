<?php

declare(strict_types=1);

namespace App\Domain\User;

readonly class UpdateUserDTO
{
    public function __construct(
        public string $firstName,
        public ?string $middleName,
        public string $lastName,
        public string $email,
        public string $mobile,
        public ?string $roleId,
        public ?int $status
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            firstName: $data['first_name'],
            middleName: $data['middle_name'] ?? null,
            lastName: $data['last_name'],
            email: $data['email'],
            mobile: $data['mobile'],
            roleId: $data['roles'] ?? null,
            status: isset($data['status']) ? (int) $data['status'] : null
        );
    }
}
