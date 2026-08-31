<?php

use Illuminate\Support\Facades\Route;
use App\Web\TestCommunication\TestCommunicationApiController;
use App\Web\TestCommunication\TestCommunicationController;

Route::get('test-communication', [TestCommunicationController::class, 'index'])->name('test-communication');
Route::post('/test-email-test', [TestCommunicationApiController::class, 'sendEmailTest'])->name('web.test-email-test');
Route::post('test-sms', [TestCommunicationController::class, 'sendSmsTest'])->name('web.test-sms-test');



