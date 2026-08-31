<?php

use Illuminate\Support\Facades\Route;
use App\Domain\WorkflowTransition\WorkflowTransitionController;

Route::group(['middleware' => 'auth'], function () {

    Route::controller(WorkflowTransitionController::class)->group(function () {
        Route::get('workflow-transitions', 'index');
        Route::get('workflow-transitions/create', 'create');
        Route::get('workflow-transitions/datalist', 'getWorkflowTransitions');
        Route::post('workflow-transitions', 'store');
        Route::get('workflow-transitions/{id}/edit', 'edit');
        Route::post('workflow-transitions-update/{id}', 'update');
        Route::delete('workflow-transitions-delete/{id}', 'destroy');
    });
});
