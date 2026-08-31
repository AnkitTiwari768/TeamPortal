<?php

namespace App\Web\EmailTemplate;

use App\Web\EmailTemplate\EmailTemplate;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public function getTemplates()
    {
        return EmailTemplate::latest()->get();
    }

    public function getById($id)
    {
        return EmailTemplate::findOrFail($id);
    }

  public function store($data, $id = null)
    {
        if ($id) {
            $template = EmailTemplate::findOrFail($id);
        } else {
            $template = new EmailTemplate();
            $template->id = uuid();
        }

        if (isset($data['subject'])) {
            $template->subject = $data['subject'];
            $template->template_key = Str::slug($data['subject']);
        }

        if (isset($data['body'])) {
            $template->body = $data['body'];
        }

        // 🔥 FINAL VARIABLES FIX
        if (isset($data['variables']) && !empty($data['variables'])) {

            $decoded = json_decode(trim($data['variables']), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $template->variables = $decoded;
            }
        }

        if (isset($data['is_active'])) {
            $template->is_active = $data['is_active'];
        }

        $template->save();

        return $template;
    }
}