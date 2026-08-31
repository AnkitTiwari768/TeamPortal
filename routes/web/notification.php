<?php

use Illuminate\Support\Facades\Route;
use App\Web\Mail\MailController;
use App\Web\Notification\NotificationController;

Route::get('/test-mail', [MailController::class, 'testMail']);


Route::group(['middleware' => 'auth'], function() {

    Route::controller(NotificationController::class)->group(function() {
        Route::get('/notifications', 'getNotifications')->name('notifications.get');

        Route::post('/notifications/mark-read', 'markAllAsRead')
            ->name('notifications.markRead');

        Route::post('/notifications/mark-read/{id}', 'markSingleRead')
            ->name('notifications.markSingleRead');
    });

});