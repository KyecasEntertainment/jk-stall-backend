<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DataAnalyticsController;
use App\Http\Controllers\CalculationController;


Route::prefix('moreventures/')->name('moreventures.')->group(function () {
    Route::get('monitoring-access', [UserController::class, 'MonitoringAccess'])->name('monitoring-access');
    Route::get('testing', [UserController::class, 'test'])->name('testing');
    Route::match(['get', 'post'], 'submit', [UserController::class, 'submitAnydeskID'])->name('submit-anydesk-id');
    Route::delete('delete/{id}', [UserController::class, 'deletePC']);
});

