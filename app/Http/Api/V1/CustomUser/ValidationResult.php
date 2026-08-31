<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

class ValidationResult 
{
    public function __construct(
        private bool $status, 
        private mixed $errors = [], 
        private ?array $validated = [])
    {
        
    }

    public function status(): bool
    {
        return $this->status;
    }

    public function errors(): mixed
    {
        return $this->errors;
    }

    public function validated(): array
    {
        return $this->validated;
    }
}