<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\CustomUser;

class Result 
{
    public function __construct(
        private bool $status, 
        private ?string $message = null,
        private mixed $data = null)
    {
        
    }

    public function status(): bool
    {
        return $this->status;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    public function data(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void 
    {
        $this->data = $data;
    }
}