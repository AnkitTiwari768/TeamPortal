<?php 

declare(strict_types=1);

namespace App\Http\Api\V1\Notification;

use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function __construct(private NotificationService $notificationService) {}

    public function getAuthNotificationCount()
    {
        return $this->success(
            $this->notificationService->getUserNotificationCount(userId: AuthId(), isRead: false)
        );
    }

    public function getAuthRecentNotifications()
    {
        return $this->success(
            $this->notificationService->getUserNotifications(userId: AuthId())
        );
    }
}