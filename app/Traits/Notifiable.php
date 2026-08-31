<?php 

declare(strict_types=1);

namespace App\Traits;

use App\Http\Api\V1\Notification\NotificationService;
use App\Enums\NotificationType;

trait Notifiable
{
    public function notify(string $userId, NotificationType $type, string $message, ?string $subject = null, ?string $fromUserId = null) : bool
    {
        return (new NotificationService())->notify($userId, $type, $message, $subject, $fromUserId);
    }

    public function notifyAll(array $userIds, NotificationType $type, string $message, ?string $subject = null, ?string $fromUserId = null) : bool
    {
        return (new NotificationService())->notifyAll($userIds, $type, $message, $subject, $fromUserId);
    }

    public function getUserNotificationCount(string $userId, bool $isRead) : int
    {
        return (new NotificationService())->getUserNotificationCount($userId, $isRead);
    }

    public function markRead(string $userNotificationId) : bool
    {
        return (new NotificationService())->markRead($userNotificationId);
    }

    public function getUserNotifications(string $userId)
    {
        return (new NotificationService())->getUserNotifications($userId);
    }
}