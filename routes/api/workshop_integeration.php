<?php

use Illuminate\Support\Facades\Route;
use App\Http\Api\V1\Workshop_Integeration\WorkshopController;


Route::get('event-card-list', [WorkshopController::class , 'getEventCards']);
Route::get('event-list', [WorkshopController::class , 'getAllList']);
Route::get('view-event-details/{id}', [WorkshopController::class , 'viewEventDetails']);
