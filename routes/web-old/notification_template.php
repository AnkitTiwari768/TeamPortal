<?php

use Illuminate\Support\Facades\Route;
use App\Web\NotificationTemplate\NotificationTemplateController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(NotificationTemplateController::class)->group(function () {

        Route::get('/notification-template', 'index')->name('notification-template-list');
        Route::get('/notification-template/create', 'create')->name('notification-template-create');
        Route::post('/notification-template/store', 'store')->name('notification-template-store');
        Route::get('/notification-template/edit/{id}', 'edit')->name('notification-template-edit');
        Route::post('/notification-template/update/{id}', 'update')->name('notification-template-update');

    });

});