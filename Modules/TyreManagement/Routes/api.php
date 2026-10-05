<?php

use Illuminate\Support\Facades\Route;
use Modules\TyreManagement\Http\Controllers\TyreManagementController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('tyremanagements', TyreManagementController::class)->names('tyremanagement');
});
