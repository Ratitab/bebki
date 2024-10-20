<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
|
|
*/
Route::post('/signup', [AuthenticationController::class, 'registration']);
Route::post('/otp-registration', [AuthenticationController::class, 'otp_registration']);
