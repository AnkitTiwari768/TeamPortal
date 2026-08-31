<?php

use Illuminate\Support\Facades\Route;
use App\Web\Timeline\TimelineController as TC;

Route::group(['middleware' => 'auth'], function () {
    Route::controller(TC::class)->group(function () {
        Route::get('/get-timeline/{id}',  'getTimelineHistory');
        Route::get('get-timeline-details/{id}', 'getTimelineHistoryDetails');
    });
});
