<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CompanyController;
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
Route::post('/otp-forgot-password', [AuthenticationController::class, 'otp_forgot_password']);
Route::post('/forgot-password', [AuthenticationController::class, 'forgot_password']);

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

    Route::put('/update-user-information', [AuthenticationController::class, 'update_user_information']);


    Route::post('/change-password', [AuthenticationController::class, 'change_password']);



    /*
    |--------------------------------------------------------------------------
    | COMPANY
    |--------------------------------------------------------------------------
    |
    |
    */
    Route::prefix('company')->group(function () {
        Route::post('/add', [CompanyController::class, 'store']);
        Route::put('/update/{company_id}', [CompanyController::class, 'update']);
    });




    Route::post('logout', [AuthenticationController::class, 'logout']);
});
