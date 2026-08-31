<?php

declare(strict_types=1);

namespace App\Domain\Batch;

final readonly class CreateBatchDto
{
    public function __construct(
        public string $claim_type_id,
        public array $claim_id,
        public ?int $is_declaration_agreed = null
    ) {}

    public static function fromValidated(array $data): self
    {
        return new self(
            claim_type_id: $data['claim_type_id'],
            claim_id: $data['claim_id'],
            is_declaration_agreed: $data['is_declaration_agreed'] ? (int) $data['is_declaration_agreed'] : null,
        );
    }
}
