<?php

use Illuminate\Support\Facades\Route;
use App\Domain\WorkflowState\WorkflowStateController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(WorkflowStateController::class)->group(function () {
        Route::get('workflow-states', 'index');
        Route::get('workflow-states/create', 'create');
        Route::get('workflow-states/datalist', 'getWorkflowStates');
        Route::post('workflow-states', 'store');
        Route::get('workflow-states/{id}/edit', 'edit');
        Route::post('workflow-states-update/{id}', 'update');
        Route::delete('workflow-states-delete/{id}', 'destroy');
    });
});
