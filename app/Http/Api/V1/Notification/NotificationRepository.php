<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Notification;

use DB;
use App\Enums\NotificationType;

class NotificationRepository 
{
    public function getNotificationTypeId(string $name) : ?int
    {
        $notificationType = DB::table('notification_types')
                                ->select('id')
                                ->where('name', $name)
                                ->first();

        return $notificationType->id ?? null;
    }

    public function createNotification(NotificationType $type, string $message, ?string $subject = null, ?string $fromUserId = null) : Notification
    {
        return Notification::create([
            'notification_type_id' => $this->getNotificationTypeId($type->value),
            'user_id' => $fromUserId ?? AuthId(),
            'message' => $message,
            'subject' => $subject,
            'created_at' => currentDateTime() 
        ]);
    }

    public function createUserNotification(string $notificationId, string $userId) : UserNotification
    {
        return UserNotification::create([
            'notification_id' => $notificationId,
            'user_id' => $userId,
        ]);
    }

    public function createAllUserNotification(string $notificationId, array $userIds) : bool
    {
        $data = [];

        foreach ($userIds as $userId) 
        {
            $data[] = [
                'id' => uuid(),
                'notification_id' => $notificationId,
                'user_id' => $userId,
            ];
        }
        
        return (bool) DB::table('user_notifications')->insert($data);
    }

    public function getUserNotificationCount(string $userId, bool $isRead) : int
    {
        $query = UserNotification::where('user_id', $userId);
		$query->where('is_read', false);
        /* if ($isRead)
        {
            $query->where('is_read', true);
        } */

        return $query->count();
    }

    public function markRead(string $userNotificationId) 
    {
        return DB::table('user_notifications')
                ->where('id', $userNotificationId)
                ->update([
                    'is_read' => true,
                    'read_at' => currentDateTime()
                ]);
    }
	
	public function allNotificationMrakReads() 
    {
        return DB::table('user_notifications')
                ->where('user_id',AuthId())
                ->update([
                    'is_read' => true,
                    'read_at' => currentDateTime()
                ]);
    }

    public function getUserNotifications(string $userId)
    {
        return DB::table('notifications AS n')
            ->selectRaw('
                un.id AS user_notification_id,
                TRIM(CONCAT_WS(" ", u.first_name, u.middle_name, u.last_name)) AS sent_by,
                TRIM(CONCAT_WS(" ", u2.first_name, u2.middle_name, u2.last_name)) AS sent_to,
                n.message,
                n.subject,
                n.created_at AS sent_at,
                un.read_at
            ')
            ->join('user_notifications AS un', 'n.id', '=', 'un.notification_id')
            ->join('users AS u', 'u.id', '=', 'n.user_id')
            ->join('users AS u2', 'u2.id', '=', 'un.user_id')
            ->where('un.user_id', $userId)
            ->where('un.is_read', false)
            ->orderBy('n.created_at', 'desc')
            //->limit(5)
            ->get();           
    }
}