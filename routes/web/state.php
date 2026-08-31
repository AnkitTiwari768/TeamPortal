<?php

use Illuminate\Support\Facades\Route;

use App\Web\State\StateController;

Route::group(['middleware' => 'auth'], function() {
    Route::controller(StateController::class)->group(function() {
        Route::get('/states',  'index')->name('state.index');
        Route::get('/states/datalist', 'getStates');
        Route::get('states/create', 'create');
        Route::get('states/{id}/edit', 'edit');
        Route::post('/states', 'createState');
        Route::post('/states/update/{id}', 'updateState');
    });

Route::get('getState/{id}', [StateController::class,'getByCountry']);
});