<?php
namespace App\Web\Notification;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    protected $listen = [
        SendNotificationEvent::class => [
            StoreNotificationListener::class,
        ],
    ];
}