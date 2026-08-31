<?php

use App\Web\ApplicationStatus\PublicStatusController;
use App\Web\ApplicationStatus\StatusTabController;
use App\Web\ApplicationStatus\FieldController;
use App\Web\ApplicationStatus\TimelineController;
use Illuminate\Support\Facades\Route;

// Admin Routes (Status Tracking Management)
Route::middleware(['auth'])->group(function () {
    
    // Status Tabs
    Route::controller(StatusTabController::class)->group(function () {
        Route::get('status-tabs', 'index')->name('status-tabs');
        Route::get('status-tabs-datalist', 'datalist')->name('status-tabs-datalist');
        Route::get('status-tabs-create', 'create')->name('status-tabs-create');
        Route::post('status-tabs-store', 'store')->name('status-tabs-store');
        Route::get('status-tabs-edit/{id}/edit', 'edit')->name('status-tabs-edit');
        Route::post('status-tabs-update/{id}', 'update')->name('status-tabs-update');
        Route::post('status-tabs-delete/{id}', 'delete')->name('status-tabs-delete');
    });

    // Tab Fields
    Route::controller(FieldController::class)->group(function () {
        Route::get('fields/{tabId}', 'index')->name('fields');
        Route::get('fields-datalist/{tabId}', 'datalist')->name('fields-datalist');
        Route::post('fields-store', 'store')->name('fields-store');
        Route::post('fields-update/{id}', 'update')->name('fields-update');
        Route::post('fields-delete/{id}', 'delete')->name('fields-delete');
    });

    // Tab Timelines
    Route::controller(TimelineController::class)->group(function () {
        Route::get('timelines/{tabId}', 'index')->name('timelines');
        Route::get('timelines-datalist/{tabId}', 'datalist')->name('timelines-datalist');
        Route::post('timelines-store', 'store')->name('timelines-store');
        Route::post('timelines-update/{id}', 'update')->name('timelines-update');
        Route::post('timelines-delete/{id}', 'delete')->name('timelines-delete');
    });
});
