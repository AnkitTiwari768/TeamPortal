<?php

use Illuminate\Support\Facades\Route;

use App\Web\AuditTrail\AuditTrailController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(AuditTrailController::class)->group(function() {
        Route::get('/audit-trail',  'index')->name('audit-trail.index');
        Route::get('/audit-trail/datalist', 'getLogs');
    });
});