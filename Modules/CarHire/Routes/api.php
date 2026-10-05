<?php

use Illuminate\Support\Facades\Route;
use Modules\CarHire\Http\Controllers\Api\V1\ProviderCarController;

Route::group(['prefix' => 'v1/provider/car', 'as' => 'api.provider.car.', 'middleware' => ['auth:api']], function () {
    Route::get('list', [ProviderCarController::class, 'index'])->name('list');
    Route::get('attributes', [ProviderCarController::class, 'getAttributes'])->name('attributes');
    Route::get('models/{brandId}', [ProviderCarController::class, 'getModelsByBrand'])->name('models');
    Route::get('models-by-brand/{brandId}', [ProviderCarController::class, 'getModelsByBrand'])->name('models-by-brand');
    Route::post('store', [ProviderCarController::class, 'store'])->name('store');
    Route::get('details/{id}', [ProviderCarController::class, 'show'])->name('details');
    Route::post('update/{id}', [ProviderCarController::class, 'update'])->name('update');
    Route::delete('delete/{id}', [ProviderCarController::class, 'destroy'])->name('delete');
    Route::post('status/{id}', [ProviderCarController::class, 'toggleStatus'])->name('status');
    Route::post('status-update', [ProviderCarController::class, 'toggleStatus'])->name('status-update');
    Route::post('status-update/{id}', [ProviderCarController::class, 'toggleStatus'])->name('status-update-param');
});
