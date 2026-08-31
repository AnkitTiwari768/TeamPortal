<?php

declare(strict_types=1);

namespace App\Domain\UserLog;

use Illuminate\Http\Resources\Json\JsonResource;

class UserLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => UserLogType::tryFrom((string) $this->action)?->label() ?? ucfirst((string) $this->action),
            'user_id' => $this->user_id,
            'user_name' => trim((string) $this->target_first_name . ' ' . (string) $this->target_last_name),
            'user_email' => $this->target_email,
            'performed_by' => $this->performed_by,
            'performed_by_name' => trim((string) $this->actor_first_name . ' ' . (string) $this->actor_last_name),
            'performed_by_email' => $this->actor_email,
            'description' => $this->description,
            'created_at' => $this->created_at?->format('d-m-Y H:i:s') ?? '',
        ];
    }
}
