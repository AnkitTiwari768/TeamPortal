<?php

declare(strict_types=1);

namespace App\Http\Api\V1\AdminWorkshop;

readonly class UploadDocumentDto
{
    public function __construct(
        // public ?string $claim_id,
        public ?string $uploaded_ids
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
           // claim_id: $data['claim_id'] ?? null,
            uploaded_ids: $data['uploaded_ids'] ?? null,
        );
    }
}
