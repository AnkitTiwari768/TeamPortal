<?php

namespace App\Web\EmailTemplate;

class EmailTemplateRequest
{
    public static function getRules(?string $id = null): array 
    {
        return [
            'subject' => 'required',
            'body' => 'required',
           'variables' => 'nullable', // 🔥 MUST ADD
            'is_active' => 'required|integer',

        ];
    }
}