<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\PawnshopProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\LimitInformationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SystemNotificationController;
use App\Http\Middleware\TrackProductViews;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use NjoguAmos\Turnstile\Http\Middleware\TurnstileMiddleware;


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
| PRODUCTS
|--------------------------------------------------------------------------
|
|
*/
Route::get('search-products', [ProductController::class, 'index']);
Route::get('single-product/{productId}', [ProductController::class, 'show'])->middleware([TrackProductViews::class]);
Route::post('get-phone/{productId}', [ProductController::class, 'getPhone'])->middleware([TurnstileMiddleware::class]);
Route::post('/upload-for-pawnshop', [PawnshopProductController::class, 'store']);

/*
|--------------------------------------------------------------------------
| COMPANIES
|--------------------------------------------------------------------------
|
|
*/
Route::get('company-list', [CompanyController::class, 'findAll']);
Route::get('company-list/{company_id}', [CompanyController::class, 'findSingle']);
Route::post('exclusive-user', [AuthenticationController::class, 'exclusive_users']);

/*
|--------------------------------------------------------------------------
| CALLBACKS
|--------------------------------------------------------------------------
|
|
*/
//Route::post('/activate-limit-callback', [LimitInformationController::class, 'activate_limits']);
Route::post('/stripe/webhook', [PaymentController::class, 'handleWebhook']);

/*
|--------------------------------------------------------------------------
| AUTHENTICATION
|--------------------------------------------------------------------------
|
|
*/
Route::post('/signup', [AuthenticationController::class, 'registration'])->middleware(TurnstileMiddleware::class);
Route::post('/signin', [AuthenticationController::class, 'login'])->middleware(TurnstileMiddleware::class);
Route::post('/otp-registration', [AuthenticationController::class, 'otp_registration'])->middleware(TurnstileMiddleware::class);
Route::post('/otp-forgot-password', [AuthenticationController::class, 'otp_forgot_password'])->middleware(TurnstileMiddleware::class);
Route::post('/forgot-password', [AuthenticationController::class, 'forgot_password'])->middleware(TurnstileMiddleware::class);

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
    Route::post('/update-email-or-phone', [AuthenticationController::class, 'update_email_or_phone'])->middleware(TurnstileMiddleware::class);
    Route::post('/upload-profile-image', [AuthenticationController::class, 'upload_images']);
    Route::post('/otp-update-email-or-phone', [AuthenticationController::class, 'otp_update_email_or_phone'])->middleware(TurnstileMiddleware::class);

    Route::put('/update-user-information', [AuthenticationController::class, 'update_user_information'])->middleware(TurnstileMiddleware::class);


    Route::post('/change-password', [AuthenticationController::class, 'change_password'])->middleware(TurnstileMiddleware::class);

    Route::get('/user/limits', [LimitInformationController::class, 'user_limits']);
    Route::get('/user/count-favourites', [ProductController::class, 'count_user_favourites']);
    Route::get('/user/favourites', [ProductController::class, 'user_favourite_products']);
    Route::get('/user/notifications', [SystemNotificationController::class, 'findMany']);



    Route::post('/set-paid-advertisement/{productId}', [ProductController::class, 'set_paid_adv']);
    /*
    |--------------------------------------------------------------------------
    | COMPANY
    |--------------------------------------------------------------------------
    |
    |
    */
    Route::prefix('company')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);

        Route::post('/add', [CompanyController::class, 'store'])->middleware(TurnstileMiddleware::class);
        Route::post('/upload-profile-image', [CompanyController::class, 'upload_images']);
        Route::put('/update/{company_id}', [CompanyController::class, 'update'])->middleware(TurnstileMiddleware::class);
        Route::delete('/delete/{company_id}', [CompanyController::class, 'delete'])->middleware(TurnstileMiddleware::class);

        Route::get('/limits/{company_id}', [LimitInformationController::class, 'company_limits']);

        Route::get('/all-limits', [CompanyController::class, 'company_limits']);

        Route::get('/{company_id}', [CompanyController::class, 'show']);
    });


    Route::prefix('product')->group(function () {
        Route::post('/add', [ProductController::class, 'store'])->middleware(TurnstileMiddleware::class);
        Route::put('/update/{product_id}', [ProductController::class, 'update'])->middleware(TurnstileMiddleware::class);
        Route::put('/update-product-order/{product_id}', [ProductController::class, 'update_product_order']);
        Route::post('/upload-images', [ProductController::class, 'upload_images']);
        Route::post('/make-favourite', [ProductController::class, 'make_favourite']);
        Route::post('/delete/{product_id}', [ProductController::class, 'delete']);

        Route::post('/sold/{product_id}', [ProductController::class, 'sold']);
    });

    Route::prefix('pawnshop-products')->middleware('auth:api')->group(function () {
        Route::get('/', [PawnshopProductController::class, 'find_many_by_pawnshop_id']);
        Route::post('/change-status', [PawnshopProductController::class, 'change_status']);
    });



    Route::prefix('payment')->group(function () {
        Route::post('/stripe/create',[PaymentController::class,'createStripePayment']);
        Route::get('/transactions',[PaymentController::class,'findManyByUserId']);
    });

//    Route::prefix('limits')->group(function () {
//        Route::post('/buy', [LimitInformationController::class, 'buy_limits']);
//    });

    Route::post('logout', [AuthenticationController::class, 'logout']);
});
