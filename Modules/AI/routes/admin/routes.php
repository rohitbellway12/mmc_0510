<?php


use Illuminate\Support\Facades\Route;
use Modules\AI\app\Http\Controllers\Admin\AIProductController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::group(['prefix' => 'product', 'as' => 'product.'], function () {
        Route::get('title-auto-fill', [AIProductController::class, 'titleAutoFill'])->name('title-auto-fill');
        Route::get('description-auto-fill', [AIProductController::class, 'descriptionAutoFill'])->name('description-auto-fill');
        Route::get('short-description-auto-fill', [AIProductController::class, 'shortDescriptionAutoFill'])->name('short-description-auto-fill');
        Route::get('general-setup-auto-fill', [AIProductController::class, 'generalSetupAutoFill'])->name('general-setup-auto-fill');
        Route::get('price-others-auto-fill', [AIProductController::class, 'pricingAndOthersAutoFill'])->name('price-others-auto-fill');
        Route::get('seo-section-auto-fill', [AIProductController::class, 'productSeoSectionAutoFill'])->name('seo-section-auto-fill');
        Route::get('variation-setup-auto-fill', [AIProductController::class, 'productVariationSetupAutoFill'])->name('variation-setup-auto-fill');
        Route::post('analyze-image-auto-fill', [AIProductController::class, 'generateTitleFromImages'])->name('analyze-image-auto-fill');
        Route::post('generate-title-suggestions', [AIProductController::class, 'generateProductTitleSuggestion'])->name('generate-title-suggestions');
    });

    Route::group(['prefix' => 'business-settings', 'as' => 'business-settings.'], function () {
        Route::group(['middleware' => ['module:business_settings']], function () {
//            Route::controller(AISettingsController::class)->group(function () {
//                Route::get('ai-settings', 'index')->name('ai-settings');
//            });
        });
    });
});
