<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Web\ApplicationStatus\StatusController;

// Public Tracking Routes
Route::get('application-status', [StatusController::class, 'index'])->name('application-status');
Route::post('application-status-check', [StatusController::class, 'check'])->name('application-status-check');
