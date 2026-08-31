<?php

declare(strict_types=1);

namespace App\Web\BonusQuery;

readonly class StoreQueryLogDto
{
    public function __construct(
        public string $query_id,
        public string $comments,
        public array $documents
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            query_id: $data['query_id'],
            comments: $data['comments'],
            documents: $data['documents']
        );
    }
}
