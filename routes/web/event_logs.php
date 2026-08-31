<?php

use Illuminate\Support\Facades\Route;
use App\Domain\EventLog\EventLogController;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(EventLogController::class)->group(function () {
        Route::get('/event-logs', 'index')->name('event-logs.index');
        Route::get('/event-logs/datalist', 'datalist');
        Route::get('/event-logs/{id}', 'show');
        Route::delete('/event-logs/{id}', 'destroy');
    });
});
