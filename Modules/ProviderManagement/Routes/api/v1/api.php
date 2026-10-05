<?php

use Illuminate\Support\Facades\Route;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Customer\FavoriteProviderController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Customer\ProviderController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\ConfigController as ProviderConfigController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\BookingReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\BusinessReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\Report\TransactionReportController;
use Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\TimeScheduleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider'], function () {
    Route::post('forgot-password', 'ProviderController@forgotPassword');
    Route::post('otp-verification', 'ProviderController@otpVerification');
    Route::put('reset-password', 'ProviderController@resetPassword');
    Route::post('change-language', 'ProviderController@changeLanguage');
    Route::get('get-all-services', 'ProviderController@getAllServices');
});

Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {

    Route::get('/', 'ProviderController@index');
    Route::get('dashboard', 'ProviderController@dashboard');
    Route::get('get-bank-details', 'ProviderController@getBankDetails');
    Route::put('update-bank-details', 'ProviderController@updateBankDetails');

    Route::get('config', [ProviderConfigController::class, 'config'])->withoutMiddleware('auth:api');
    Route::get('config/page-details/{key}', [ProviderConfigController::class, 'pageDetails'])->withoutMiddleware('auth:api');

    Route::get('info', 'ProviderController@index');
    Route::get('adjust', 'ProviderController@adjust');
    Route::get('notifications', 'ProviderController@notifications');
    Route::put('update/fcm-token', 'ProviderController@updateFcmToken');
    Route::put('update/profile', 'ProviderController@updateProfile');
    Route::put('update/password', 'ProviderController@updatePassword');
    Route::post('update/tutorial', 'ProviderController@updateTutorial');
    Route::get('config/get-routes', [ProviderConfigController::class, 'getRoutes']);
    Route::get('config/get-car-types', [ProviderConfigController::class, 'getCarTypes']);
    Route::delete('delete', 'ProviderController@deleteProvider');
    Route::get('transaction', 'ProviderController@transaction');
    Route::post('toggle-emergency', 'ProviderController@toggleEmergencyAvailability');
    Route::post('update-emergency-settings', 'ProviderController@updateEmergencySettings');

    Route::get('subscribed/sub-categories', 'ProviderController@subscribedSubCategories');


    Route::group(['prefix' => 'service', 'as' => 'service.',], function () {
        Route::get('available-services', 'ServiceController@availableServices');
        Route::post('update-subscription', 'ServiceController@updateSubscription');
        Route::post('update-service-details', 'ServiceController@updateServiceDetails');
        Route::get('service-details', 'ServiceController@getServiceDetails');
    });

    Route::group(['prefix' => 'account', 'as' => 'account.',], function () {
        Route::get('overview', 'AccountController@overview');
        Route::get('account-edit', 'AccountController@accountEdit');
        Route::put('account-update', 'AccountController@accountUpdate');
        Route::get('commission-info', 'AccountController@commissionInfo');
        Route::post('pay-to-admin', 'AccountController@payToAdmin');
    });

    Route::resource('withdraw', 'WithdrawController', ['only' => ['index', 'store']]);

    Route::group(['prefix' => 'payment-information', 'as' => 'payment-information.'], function () {
        Route::get('index', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationIndex'])->name('index');
        Route::post('store', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationStore'])->name('store');
        Route::get('edit/{id}', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationEdit'])->name('edit');
        Route::post('update/{id}', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationUpdate'])->name('update');
        Route::get('status-update/{id}', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationStatusUpdate'])->name('status-update');
        Route::get('default-status-update/{id}', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationDefaultStatusUpdate'])->name('default-status-update');
        Route::delete('delete/{id}', [Modules\ProviderManagement\Http\Controllers\Api\V1\Provider\WithdrawController::class, 'paymentInformationDelete'])->name('delete');
    });

    Route::get('review', 'ProviderController@review');
    Route::get('get-terms-and-conditions', 'ProviderController@getTermsAndConditions');
    Route::post('update-terms-and-conditions', 'ProviderController@updateTermsAndConditions');
    Route::get('get-about-us', 'ProviderController@getAboutUs');
    Route::post('update-about-us', 'ProviderController@updateAboutUs');

    Route::get('available-time-schedule', [TimeScheduleController::class, 'getAvailableTimeSchedule']);
    Route::put('available-time-schedule', [TimeScheduleController::class, 'setAvailableTimeSchedule']);

    //REPORT
    Route::group(['prefix' => 'report', 'namespace' => 'Report'], function () {
        //Transaction Report
        Route::post('transaction', [TransactionReportController::class, 'getTransactionReport']);
        Route::post('transaction/download', [TransactionReportController::class, 'downloadTransactionReport']);

        //Booking Report
        Route::post('booking', [BookingReportController::class, 'getBookingReport']);
        Route::post('booking/download', [BookingReportController::class, 'getBookingReportDownload']);

        //Business Report
        Route::group(['prefix' => 'business', 'as' => 'business.'], function () {
            Route::post('overview', [BusinessReportController::class, 'getBusinessOverviewReport']);
            Route::post('earning', [BusinessReportController::class, 'getBusinessEarningReport']);
            Route::post('expense', [BusinessReportController::class, 'getBusinessExpenseReport']);
        });
    });
});

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer'], function () {
    Route::group(['prefix' => 'provider', 'as' => 'provider.'], function () {
        Route::post('list', [ProviderController::class, 'getProviderList']);
        Route::get('list-by-sub-category', [ProviderController::class, 'getProviderListBySubCategory']);
        Route::post('search-by-service', [ProviderController::class, 'getProvidersByServiceAndDetails']);
    });

    Route::group(['prefix' => 'favorite', 'as' => 'favorite.', 'middleware' => ['auth:api']], function () {
        Route::get('provider-list', [FavoriteProviderController::class, 'list']);
        Route::post('provider', [FavoriteProviderController::class, 'store']);
        Route::post('provider-destroy/{provider_id}', [FavoriteProviderController::class, 'destroy']);
    });

    Route::get('provider-details', [ProviderController::class, 'getProviderDetails']);

    Route::post('available-provider', [ProviderController::class, 'getAvailableProvider']);
    Route::post('available-service', [ProviderController::class, 'getAvailableService']);
    Route::post('rebooking-information', [ProviderController::class, 'rebookingInformation']);

    // Emergency providers — get all providers with emergency mode ON
    Route::get('emergency-providers', [ProviderController::class, 'getEmergencyProviders']);
});

//admin
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::resource('provider', 'ProviderController', ['only' => ['index', 'store', 'edit', 'update']]);
    Route::group(['prefix' => 'provider', 'as' => 'provider.',], function () {
        Route::get('data/overview/{user_id}', 'ProviderController@overview');
        Route::put('settings/update/{provider_id}', 'ProviderController@settingsUpdate');

        Route::put('status/update', 'ProviderController@statusUpdate');
        Route::delete('delete', 'ProviderController@destroy');
        Route::delete('remove-image', 'ProviderController@removeImage');

        Route::get('data/reviews/{provider_id}', 'ProviderController@reviews');
        Route::get('data/requests', 'ProviderController@providerRequest');
        Route::get('data/requests/search', 'ProviderController@searchRequest');
        Route::get('data/serviceman/list/{provider_id}', 'ProviderController@servicemanList');

        Route::get('data/bookings/{provider_id}', 'ProviderController@bookings');
        Route::get('subscribed/sub-categories/{provider_id}', 'ProviderController@subscribedSubCategories');
        Route::put('update-subscription/sub-categories/{provider_id}', 'ProviderController@updateSubscription');
    });
});
