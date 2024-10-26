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
Route::post('/signin', [AuthenticationController::class, 'login']);
Route::post('/otp-registration', [AuthenticationController::class, 'otp_registration']);

/*
|--------------------------------------------------------------------------
| AUTHORIZED ENDPOINTS
|--------------------------------------------------------------------------
|
|
*/
Route::middleware(['auth:api'])->group(function () {
    Route::get('/auth/check', function(Request $request){
        return auth()->user();
    });
    Route::post('/update-email-or-phone', [AuthenticationController::class, 'update_email_or_phone']);

    Route::post('/otp-update-email-or-phone', [AuthenticationController::class, 'otp_update_email_or_phone']);


    Route::post('/change-password', [AuthenticationController::class, 'change_password']);

    Route::post('logout', [AuthenticationController::class, 'logout']);
});
