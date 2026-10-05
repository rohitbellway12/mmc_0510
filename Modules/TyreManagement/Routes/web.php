<?php

use Illuminate\Support\Facades\Route;
use Modules\TyreManagement\Http\Controllers\TyreManagementController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('tyremanagements', TyreManagementController::class)->names('tyremanagement');
});
