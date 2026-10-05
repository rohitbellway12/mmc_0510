<?php

use Illuminate\Support\Facades\Route;
use Modules\BookingModule\Http\Controllers\Api\V1\Customer\BookingController;
use Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingController as ProviderBookingController;
use Modules\BookingModule\Http\Controllers\Api\V1\Serviceman\BookingController as ServicemanBookingController;

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

Route::group(['prefix' => 'customer', 'as' => 'customer.', 'namespace' => 'Api\V1\Customer', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::get('/', [BookingController::class, 'index']);
        Route::get('with-car', [BookingController::class, 'withCarDetails']);
        Route::get('/{booking_id}', [BookingController::class, 'show']);
        Route::get('single/{booking_id}', [BookingController::class, 'singleDetails']);
        Route::get('provider/slots', [BookingController::class, 'getAvailableSlots'])->withoutMiddleware('auth:api');
        Route::get('provider/questions', [BookingController::class, 'getProviderQuestions'])->withoutMiddleware('auth:api');
        Route::post('request/send', [BookingController::class, 'placeRequest'])->middleware('hitLimiter')->withoutMiddleware('auth:api');
        Route::put('status-update/{booking_id}', [BookingController::class, 'statusUpdate']);
        Route::post('single-repeat-cancel/{repeat_id}', [BookingController::class, 'singleBookingCancel']);
        Route::post('track/{readable_id}', [BookingController::class, 'track'])->withoutMiddleware('auth:api');
        Route::post('store-offline-payment-data', [BookingController::class, 'storeOfflinePaymentData'])->withoutMiddleware('auth:api');
        Route::post('switch-payment-method', [BookingController::class, 'switchPaymentMethod'])->withoutMiddleware('auth:api');
    });

    Route::group(['prefix' => 'car', 'as' => 'car.'], function () {
        Route::get('list', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'index'])->withoutMiddleware('auth:api');
        Route::get('details/{id}', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'show'])->withoutMiddleware('auth:api');
        Route::post('book', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'store']);

        // Chauffeur Service Specific Routes
        Route::get('types', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'getCarTypes'])->withoutMiddleware('auth:api');
        Route::post('chauffeur/search', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'searchChauffeurCars'])->withoutMiddleware('auth:api');
        Route::post('chauffeur/book', [\Modules\CarHire\Http\Controllers\Api\V1\CustomerCarController::class, 'bookChauffeur']);
    });

    Route::group(['prefix' => 'estimate', 'as' => 'estimate.'], function () {
        Route::get('{token}', [\Modules\BookingModule\Http\Controllers\Api\V1\Customer\BookingEstimateController::class, 'details'])->withoutMiddleware('auth:api');
        Route::post('accept', [\Modules\BookingModule\Http\Controllers\Api\V1\Customer\BookingEstimateController::class, 'accept'])->withoutMiddleware('auth:api');
    });
});
Route::any('digital-payment-booking-response', [BookingController::class, 'digitalPaymentBookingResponse']);

Route::group(['prefix' => 'admin', 'as' => 'admin.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::post('/', 'BookingController@index');
        Route::get('{id}', 'BookingController@show');
        Route::put('status-update/{booking_id}', 'BookingController@status_update');
        Route::put('schedule-update/{booking_id}', 'BookingController@schedule_update');
        Route::get('data/download', 'BookingController@download');
    });
});


Route::group(['prefix' => 'provider', 'as' => 'provider.', 'namespace' => 'Api\V1\Provider', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::post('/', 'BookingController@index');
        Route::get('{id}', 'BookingController@show');
        Route::get('single/{id}', 'BookingController@singleDetails');
        Route::put('request-accept/{booking_id}', 'BookingController@requestAccept');
        Route::post('request-ignore/{booking_id}', 'BookingController@requestIgnore');
        Route::post('single-repeat-cancel/{repeat_id}', 'BookingController@singleBookingCancel');
        Route::put('single-repeat-status-update/{repeat_id}', 'BookingController@singleBookingStatusUpdate');
        Route::put('status-update/{booking_id}', 'BookingController@statusUpdate');
        Route::put('schedule-update/{booking_id}', 'BookingController@scheduleUpdate');
        Route::put('assign-serviceman/{booking_id}', 'BookingController@assignServiceman');
        Route::get('data/download', 'BookingController@download');

        Route::get('opt/notification-send', 'BookingController@notificationSend');

        Route::get('service/info', [ProviderBookingController::class, 'getServiceInfo']);
        Route::put('service/edit/update-booking', [ProviderBookingController::class, 'updateBooking']);
        Route::put('repeat/service/edit/update-booking', [ProviderBookingController::class, 'updateBookingRepeat']);
        Route::put('service/edit/remove-service', [ProviderBookingController::class, 'removeService']);
        Route::post('change-service-location', [ProviderBookingController::class, 'changeServiceLocation']);

    });

    Route::group(['prefix' => 'estimate', 'as' => 'estimate.'], function () {
        Route::get('search-customer', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'searchCustomer']);
        Route::get('services', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'getServices']);
        Route::get('cars', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'getCars']);
        Route::post('create', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'store']);
        Route::get('list', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'index']);
        Route::get('details/{id}', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'show']);
        Route::post('cancel/{id}', [\Modules\BookingModule\Http\Controllers\Api\V1\Provider\BookingEstimateController::class, 'cancel']);
    });
});


Route::group(['prefix' => 'serviceman', 'as' => 'serviceman.', 'namespace' => 'Api\V1\Serviceman', 'middleware' => ['auth:api']], function () {
    Route::group(['prefix' => 'booking', 'as' => 'booking.'], function () {
        Route::put('status-update/{booking_id}', 'BookingController@statusUpdate');
        Route::put('single-repeat-status-update/{booking_id}', 'BookingController@singleBookingStatusUpdate');
        Route::put('payment-status-update/{booking_id}', 'BookingController@paymentStatusUpdate');
        Route::get('list', 'BookingController@bookingList');
        Route::get('detail/{id}', 'BookingController@bookingDetails');
        Route::get('single/detail/{id}', 'BookingController@singleBookingDetails');

        Route::get('opt/notification-send', 'BookingController@notificationSend');

        Route::get('service/info', [ServicemanBookingController::class, 'getServiceInfo']);
        Route::put('service/edit/update-booking', [ServicemanBookingController::class, 'updateBooking']);
        Route::put('repeat/service/edit/update-booking', [ServicemanBookingController::class, 'updateBookingRepeat']);
        Route::put('service/edit/remove-service', [ServicemanBookingController::class, 'removeService']);
    });
});
