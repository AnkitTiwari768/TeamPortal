<?php

namespace App\Web\NotificationTemplate;

class NotificationTemplateRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
            'stage' => 'required|max:255',
            'trigger_point' => 'required|max:255',
            'message' => 'required',
            'type' => 'required|integer',
            'status' => 'required|integer',

            // optional validation
            'template_key' => 'nullable|unique:notification_templates,template_key,' . $id,
        ];
    }
}