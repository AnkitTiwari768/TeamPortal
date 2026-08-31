<?php

namespace App\Web\Notification;

use App\Web\Notification\SendNotificationEvent;
use App\Web\Notification\Notification;
use App\Web\Notification\NotificationTemplate;
use Illuminate\Support\Str;

class StoreNotificationListener
{
    public function handle(SendNotificationEvent $event)
    {
        $template = NotificationTemplate::where('template_key', $event->templateKey)
                        ->where('status', 1)
                        ->first();

        if (!$template) {
            return;
        }

        // ✅ base message
        $message = $template->message ?? '';

        // ✅ direct message
        if (is_string($event->message)) {
            $message = $event->message;
        }

        // ✅ array → dynamic replace
        if (is_array($event->message)) {
            $message = $this->replacePlaceholders($message, $event->message);
        }

        // ✅ insert
        Notification::create([
            'id'            => (string) Str::uuid(),
            'from_user_id'  => $event->fromUserId,
            'to_user_id'    => $event->toUserId,
            'form_role'     => $event->formRole,
            'to_role'       => $event->toRole,
            'type'          => $event->type,
            'template_id'   => $template->id,
            'message'       => $message,
            'is_read'       => 0,
            'status'        => 1,
        ]);
    }

    private function replacePlaceholders($message, $data = [])
    {
        if (!is_array($data)) {
            return $message;
        }

        $originalMessage = $message;

        // ✅ Step 1: Named placeholders replace
        foreach ($data as $key => $value)
        {
            $message = str_ireplace("{{$key}}", "<b>{$value}</b>", $message);

            $formattedKey = ucwords(strtolower(str_replace('_', ' ', $key)));
            $message = str_ireplace("{{$formattedKey}}", "<b>{$value}</b>", $message);
        }

        // ✅ Step 2: Sequential {00}
        if (str_contains($message, '{00}')) 
        {
            foreach (array_values($data) as $value) 
            {
                $message = preg_replace('/\{00\}/', $value, $message, 1);
            }
        }

        // ✅ Step 3: If nothing replaced → auto append
        if ($message === $originalMessage) 
        {
            $extra = collect($data)
                ->map(fn($v, $k) => ucfirst(strtolower(str_replace('_', ' ', $k))) . ": " . $v)
                ->implode(', ');

            $message .= " (" . $extra . ")";
        }

        return $message;
    }

}