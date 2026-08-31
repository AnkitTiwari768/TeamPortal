<?php
namespace App\Web\Notification;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SendNotificationEvent
{
    use Dispatchable, SerializesModels;

    public $templateKey;
    public $fromUserId;
    public $toUserId;
    public $formRole;
    public $toRole;
    public $message;
    public $type;

    public function __construct(
        $templateKey,   // ✅ now required
        $fromUserId = null,
        $toUserId = null,
        $formRole = null,
        $toRole = null,
        $type =null,
        $message = []
    ) {
        $this->templateKey = $templateKey;
        $this->fromUserId  = $fromUserId;
        $this->toUserId    = $toUserId;
        $this->formRole    = $formRole;
        $this->toRole      = $toRole;
        $this->message     = $message;
        $this->type        = $type;
    }
}