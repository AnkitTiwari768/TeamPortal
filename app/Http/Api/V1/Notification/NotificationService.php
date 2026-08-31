<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Notification;

use App\Core\BaseService;
use App\Enums\NotificationType;
use DB;

class NotificationService extends BaseService 
{
    public static function getNotificationRepository() : NotificationRepository
    {
        return new NotificationRepository();
    }

    public function notify(string $userId, NotificationType $type, string $message, ?string $subject = null, ?string $fromUserId = null) : bool
    {
        return DB::transaction(function () use ($userId, $type, $message, $subject, $fromUserId) {
            
            $notificationRepository = self::getNotificationRepository();

            if (! ($notification = $notificationRepository->createNotification($type, $message, $subject, $fromUserId))) 
            {
                return false;
            }
            
            if (! $notificationRepository->createUserNotification($notification->id, $userId))
            {
                return false;
            }

            return true;
        });
    }

    public function notifyAll(array $userIds, NotificationType $type, string $message, ?string $subject = null, ?string $fromUserId = null) : bool
    {
        return DB::transaction(function () use ($userIds, $type, $message, $subject, $fromUserId) {
            
            $notificationRepository = self::getNotificationRepository();

            if (! ($notification = $notificationRepository->createNotification($type, $message, $subject, $fromUserId))) 
            {
                return false;
            }
            
            if (! $notificationRepository->createAllUserNotification($notification->id, $userIds))
            {
                return false;
            }

            return true;
        });
    }

    public function getUserNotificationCount(string $userId, bool $isRead) : int
    {
        return (int) self::getNotificationRepository()->getUserNotificationCount($userId, $isRead);
    }

    public function markRead(string $userNotificationId)  
    {
        return self::getNotificationRepository()->markRead($userNotificationId);
    }
	
	 public function allNotificationMrakReads()  
    {
        return self::getNotificationRepository()->allNotificationMrakReads();
    }

    public function getUserNotifications(string $userId)
    {
        return self::getNotificationRepository()->getUserNotifications($userId);
    }
}