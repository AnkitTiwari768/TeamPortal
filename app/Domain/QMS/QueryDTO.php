<?php

declare(strict_types=1);

namespace App\Domain\QMS;

readonly class QueryDTO
{
    public function __construct(
        public ?string $id,
        public string $senderId,
        public ?string $senderRoleId,
        public ?string $receiverId,
        public ?string $receiverRoleId,
        public string $subject,
        public string $message,
        public array $attachments = []
    ) {}

    public static function fromRequest(array $payload, string $senderId, ?string $senderRoleId = null): self
    {
        return new self(
            id: null,
            senderId: $senderId,
            senderRoleId: $senderRoleId,
            receiverId: $payload['receiver_id'] ?? null,
            receiverRoleId: $payload['receiver_role_id'] ?? null,
            subject: $payload['subject'],
            message: $payload['message'],
            attachments: $payload['attachments'] ?? []
        );
    }
}
