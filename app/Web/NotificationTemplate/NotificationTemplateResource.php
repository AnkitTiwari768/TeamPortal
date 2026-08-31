<?php

namespace App\Web\NotificationTemplate;

use Illuminate\Http\Resources\Json\JsonResource;

class NotificationTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'stage' => $this->stage,
            'template_key' => $this->template_key,
            'trigger_point' => $this->trigger_point,
            'message' => $this->message,
            'type' => $this->type,
            'status' => $this->status,
        ];
    }
}