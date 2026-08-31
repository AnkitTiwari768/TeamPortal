<?php

declare(strict_types=1);

namespace App\Web\ApplicationWorkflow;

readonly class ApplicationWorkflowDto
{
    public function __construct(
        public string $application_id,
        public int $action,
		public ?string $revert_to,
        public ?string $comments
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            application_id: $data['application_id'],
            action: (int) $data['action'],
			revert_to: $data['revert_to'],
            comments: $data['comments'] ?? null,
        );
    }
}
