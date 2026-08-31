<?php

namespace App\Web\EmailTemplate;

use Illuminate\Http\Resources\Json\JsonResource;

class EmailTemplateResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'subject' => $this->subject,
            'variables' => $this->variables,
            'is_active' => $this->is_active,
        ];
    }
}