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



    Route::post('logout', [AuthenticationController::class, 'logout']);
});
