<?php

declare(strict_types=1);

namespace App\Domain\QMS;

use Illuminate\Http\Resources\Json\JsonResource;

class QueryListResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'status' => $this->status,
            'updated_at' => $this->updated_at->toDateTimeString(),
            'sender_name' => $this->getSenderName(),
            'receiver_name' => $this->getReceiverName(),
            'unread_count' => $this->getUnreadCountForUser(AuthId()),
        ];
    }

    private function getSenderName(): string
    {
        $name = ($this->sender->first_name ?? '') . ' ' . ($this->sender->last_name ?? '');
        $role = $this->senderRole->name ?? 'User';
        return trim($name) . " ($role)";
    }

    private function getReceiverName(): string
    {
        if ($this->receiver) {
            return ($this->receiver->first_name ?? '') . ' ' . ($this->receiver->last_name ?? '');
        }
        
        return $this->receiverRole->name ?? 'Unassigned';
    }
}
