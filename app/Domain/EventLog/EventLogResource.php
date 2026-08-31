<?php

declare(strict_types=1);

namespace App\Domain\EventLog;

use Illuminate\Http\Resources\Json\JsonResource;

class EventLogResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'event_name' => $this->event_name,
            'executed_at' => $this->executed_at ? date('d-m-Y H:i:s', strtotime($this->executed_at)) : '',
        ];
    }
}
