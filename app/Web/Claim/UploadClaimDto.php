<?php

declare(strict_types=1);

namespace App\Web\Claim;

readonly class UploadClaimDto
{
    public function __construct(
        public ?string $claim_id,
        public ?string $document_category_id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            claim_id: $data['claim_id'] ?? null,
            document_category_id: $data['document_category_id'] ?? null,
        );
    }
}
