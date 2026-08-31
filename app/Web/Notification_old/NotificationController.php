<?php 

declare(strict_types=1);

namespace App\Web\Notification;
use App\Http\Controllers\ClientController;
use App\Http\Api\V1\Notification\NotificationService;
use Illuminate\View\View;
use Illuminate\Http\Request;

class NotificationController extends ClientController
{
    public function __construct(private NotificationService $NotificationService) {}
	
	public function getAuthNotificationCount()
    {
		return $this->success($this->NotificationService->getUserNotificationCount(userId: AuthId(), isRead: false));
    }
	
	public function getUserNotifications()
    {
		return $this->success($this->NotificationService->getUserNotifications(userId: AuthId()));
    }
	
	
	public function singleNotificationMrakRead($userNotificationId)
    {
		return $this->deleted($this->NotificationService->markRead($userNotificationId));
    }
	
	public function allNotificationMrakReads()
    {
		return $this->deleted($this->NotificationService->allNotificationMrakReads());
    }
	
	
	
	

}