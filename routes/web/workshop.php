<?php

use Illuminate\Support\Facades\Route;

use App\Web\Workshop\WorkshopController;
use App\Web\Workshop\ExecutedWorkshopController;


Route::group(['middleware' => 'auth'], function () {

    Route::controller(WorkshopController::class)->group(function () {
        // Renamed proposed-workshop routes
        Route::get('proposed-workshop', 'index')->name('proposed-workshop')->middleware('permission:workshop-management-view');
        Route::get('proposed-workshop-list', 'getDataTable')->name('proposed-workshop-list')->middleware('permission:workshop-management-view');
        Route::get('create-proposed-workshop', 'createPage')->name('create-proposed-workshop')->middleware('permission:workshop-management-create');
        Route::post('store-proposed-workshop', 'storeEvent')->name('store-proposed-workshop')->middleware('permission:workshop-management-create');
        Route::get('edit-proposed-workshop/{id}/edit', 'editPage')->name('edit-proposed-workshop')->middleware('permission:workshop-management-edit');
        Route::post('update-proposed-workshop/{id}', 'updateEvent')->name('update-proposed-workshop')->middleware('permission:workshop-management-edit');
        Route::get('view-proposed-workshop/{id}', 'show')->name('view-proposed-workshop')->middleware('permission:workshop-management-view');
        Route::post('upload-proposed-workshop-image', 'uploadEventImage')->name('upload-proposed-workshop-image')->middleware('permission:workshop-management-create');
        Route::get('delete-proposed-workshop/{id}', 'deleteEvent')->name('delete-proposed-workshop')->middleware('permission:workshop-management-delete');

       
        // Backward compatibility routes to ensure existing functionality does not break
        //Route::get('event', 'index')->name('event')->middleware('permission:workshop-management-view');
        Route::get('event-list', 'getDataTable')->name('event-list')->middleware('permission:workshop-management-view');
        Route::get('create-event', 'createPage')->name('create-event')->middleware('permission:workshop-management-create');
        Route::post('store-event', 'storeEvent')->name('store-event')->middleware('permission:workshop-management-create');
        Route::get('edit-event/{id}/edit', 'editPage')->name('edit-event')->middleware('permission:workshop-management-edit');
        Route::post('update-event/{id}', 'updateEvent')->name('update-event')->middleware('permission:workshop-management-edit');
       // Route::get('view-event/{id}', 'show')->name('view-event')->middleware('permission:workshop-management-view');
        Route::post('upload-event-image', 'uploadEventImage')->name('upload-event-image')->middleware('permission:workshop-management-create');
        Route::get('delete-event/{id}', 'deleteEvent')->name('delete-event')->middleware('permission:workshop-management-delete');

        
        Route::post('/exist-district-workshop', 'checkDistrictWorkshop')->name('exist-district-workshop');
    });

    Route::controller(ExecutedWorkshopController::class)->group(function () {
        Route::get('executed-workshop', 'index')->name('executed-workshop')->middleware('permission:executed-workshop-view');
        Route::get('executed-workshop-list', 'getDataTable')->name('executed-workshop-list')->middleware('permission:executed-workshop-view');
        Route::get('edit-executed-workshop/{id}/edit', 'editPage')->name('edit-executed-workshop')->middleware('permission:executed-workshop-edit');
        Route::post('update-executed-workshop/{id}', 'updateEvent')->name('update-executed-workshop')->middleware('permission:executed-workshop-edit');
        Route::post('upload-executed-workshop-document', 'uploadSupportingDocument')->name('upload-executed-workshop-document')->middleware('permission:executed-workshop-edit');
        Route::get('view-executed-workshop/{id}', 'show')->name('view-executed-workshop')->middleware('permission:executed-workshop-view');
        Route::post('executed-workshop/{id}/change-status', 'changeStatus')->name('executed-workshop.change-status')->middleware('permission:executed-workshop-edit');

        
    });
});
Route::get('/get-executed-workshop-data/{id}', [ExecutedWorkshopController::class, 'getWorkshopData'])->name('get.executed.workshop.data');
Route::get('proposed-workshop-card-list', [WorkshopController::class], 'getEventCards')->name('proposed-workshop-card-list');
Route::get('event-card-list', [WorkshopController::class], 'getEventCards')->name('event-card-list');
