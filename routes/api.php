<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| COUNTRY
|--------------------------------------------------------------------------
|
|
*/
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/cities', [CountryController::class, 'citiesFindByCountryId']);

/*
|--------------------------------------------------------------------------
| p[roducts
|--------------------------------------------------------------------------
|
|
*/
Route::get('search-products', [ProductController::class, 'index']);
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
        $user = auth()->user();
        return $user->append('information');
    });
    Route::post('/update-email-or-phone', [AuthenticationController::class, 'update_email_or_phone']);
    Route::post('/upload-profile-image', [AuthenticationController::class, 'upload_images']);
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
        Route::get('/', [CompanyController::class, 'index']);
        Route::get('/{company_id}', [CompanyController::class, 'show']);
        Route::post('/add', [CompanyController::class, 'store']);
        Route::post('/upload-profile-image', [CompanyController::class, 'upload_images']);
        Route::put('/update/{company_id}', [CompanyController::class, 'update']);
        Route::delete('/delete/{company_id}', [CompanyController::class, 'delete']);
    });


    Route::prefix('product')->group(function () {
        Route::post('/add', [ProductController::class, 'store']);
        Route::post('/upload-images', [ProductController::class, 'upload_images']);
    });




    Route::post('logout', [AuthenticationController::class, 'logout']);
});
