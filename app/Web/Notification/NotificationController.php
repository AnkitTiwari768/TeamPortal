<?php

declare(strict_types=1);

namespace App\Web\Notification;

use App\Http\Controllers\ClientController;
use App\Web\Notification\NotificationService;

class NotificationController extends ClientController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function getNotifications()
    {
        $data = $this->notificationService->getNotifications();
        return response()->json($data);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead();
        return response()->json(['status' => 'success']);
    }

    public function markSingleRead($id)
    {
        $this->notificationService->markSingleRead($id);
        return response()->json(['status' => 'success']);
    }
}