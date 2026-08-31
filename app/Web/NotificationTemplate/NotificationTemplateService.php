<?php

namespace App\Web\NotificationTemplate;

use App\Web\Notification\NotificationTemplate;
use Illuminate\Support\Str;

class NotificationTemplateService
{
    // ✅ SIMPLE DATA RETURN (Datatable friendly)
    public function getTemplates()
    {
        return NotificationTemplate::latest()->get();
    }

    public function getById($id)
    {
        return NotificationTemplate::findOrFail($id);
    }

    public function store($data, $id = null)
    {
        if ($id) {
            $template = NotificationTemplate::findOrFail($id);
        } else {
            $template = new NotificationTemplate();
            $template->id = uuid();
        }

        $template->stage = $data['stage'];
        $template->trigger_point = $data['trigger_point'];

        // 🔥 AUTO SLUG GENERATE
        $template->template_key = Str::slug($data['trigger_point']);

        $template->message = $data['message'];
        $template->type = $data['type'];
        $template->status = $data['status'];

        $template->save();

        return $template;
    }
}