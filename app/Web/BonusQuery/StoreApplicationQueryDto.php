<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

readonly class StoreApplicationQueryDto
{
    public function __construct(
        public string $msme_id,
        public string $remarks,
        public string $claim_type_id,
        public array $file_upload_ids,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            msme_id: (string) $data['msme_id'],
            remarks: (string) $data['remarks'],
            claim_type_id: (string) $data['claim_type_id'],
            file_upload_ids: $data['file_upload_ids'] ?? [],
        );
    }
}
