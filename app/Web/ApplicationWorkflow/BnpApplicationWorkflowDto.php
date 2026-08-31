<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

readonly class BnpApplicationWorkflowDto
{
    public function __construct(
        public string $application_id,
        public int $action,
        public ?string $comments
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            application_id: $data['application_id'],
            action: (int) $data['action'],
            comments: $data['comments'] ?? null,
        );
    }
}
