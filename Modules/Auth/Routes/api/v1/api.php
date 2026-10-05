<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'namespace' => 'Api\V1'], function () {

    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('login', 'LoginController@adminLogin')->name('login');
    });

});

Route::group(['prefix' => 'provider', 'as' => 'provider', 'namespace' => 'Api\V1'], function () {

    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('registration-step-1', 'RegisterController@providerRegistrationStep1')->name('registration-step-1');
        Route::post('verification', 'RegisterController@providerVerification')->name('verification');
        Route::post('resend-otp', 'RegisterController@providerResendOtp')->name('resend-otp');
        Route::post('registration', 'RegisterController@providerRegister')->name('registration')->middleware('auth:api');
        Route::post('login', 'LoginController@providerLogin')->name('login');
       Route::post('otp-login', 'LoginController@providerOtpLogin')->name('otp-login');

    });

    Route::group(['prefix' => 'auth', 'as' => 'auth.', 'middleware' => ['auth:api']], function () {
        Route::get('categories', 'RegisterController@getAvailableServices');
        Route::get('category/{categoryId}/services', 'RegisterController@getCategoryServices');
        Route::post('update-services', 'RegisterController@updateServices');
        Route::post('update-business-info', 'RegisterController@updateBusinessInfo');
    });

});

Route::group(['prefix' => 'customer', 'as' => 'customer', 'namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('registration', 'RegisterController@customerRegister')->name('registration');
        Route::post('verify-otp', 'RegisterController@customerVerifyOtp')->name('verify-otp');
        Route::post('resend-otp', 'RegisterController@customerResendOtp')->name('resend-otp');
        Route::post('login', 'LoginController@customerLogin')->name('login');
        Route::post('otp-login', 'LoginController@customerOtpLogin')->name('otp-login');
        Route::post('social-login', 'LoginController@customerSocialLogin')->name('social-login');
        Route::post('existing-account-check', 'LoginController@existingAccountCheck');
        Route::post('registration-with-social-media', 'LoginController@registrationWithSocialMedia');
        Route::post('logout', 'LoginController@customerLogOut')->middleware('auth:api');
          
    Route::delete('remove-account', 'LoginController@removeAccount')->middleware('auth:api');

        
    });
});

Route::group(['prefix' => 'serviceman', 'as' => 'serviceman', 'namespace' => 'Api\V1'], function () {
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::post('login', 'LoginController@servicemanLogin')->name('login');
    });
});


Route::group(['prefix' => 'user', 'as' => 'user.', 'middleware' => ['auth:api'], 'namespace' => 'Api\V1'], function () {
    Route::post('logout', 'LoginController@logout')->name('logout');
});

