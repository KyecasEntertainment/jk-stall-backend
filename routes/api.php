<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DataAnalyticsController;
use App\Http\Controllers\CalculationController;

Route::prefix('inventory/')->name('inventory.')->group(function () {

    Route::prefix('products/')->name('products.')->group(function () {
        Route::post('create', [ProductController::class, 'createProduct'])->name('create-product');
        Route::get('view', [ProductController::class, 'viewProducts'])->name('view-products');
        Route::post('update/{id}', [ProductController::class, 'updateProductName'])->name('update-product');
        Route::delete('delete/{id}', [ProductController::class, 'deleteProduct'])->name('delete-product');
    });

    Route::prefix('batch/')->name('batch.')->group(function () {
        Route::post('create', [ProductController::class, 'createBatch'])->name('create-batch');
        Route::get('view', [ProductController::class, 'viewBatches'])->name('view-batches');
        Route::post('update/{batch_id}/{product_id}', [ProductController::class, 'updateBatch'])->name('update-batch');
        Route::delete('delete/{id}', [ProductController::class, 'deleteBatch'])->name('delete-batch');
        Route::delete('delete-product/{id}', [ProductController::class, 'deleteProductFromBatch'])->name('delete-product-from-batch');
    });

    Route::prefix('analytics/')->name('analytics.')->group(function () {
        Route::get('fetch', [DataAnalyticsController::class, 'calculateAnalytics'])->name('calculate-analytics');
    });

    Route::get('view-products', [ProductController::class, 'viewProductsQuantity'])->name('view-products');

    Route::post('display-product', [ProductController::class, 'displayProductsQuantity'])->name('display-product');
    Route::post('return-product', [ProductController::class, 'returnProductsQuantity'])->name('back-product');
    Route::post('discard-product', [ProductController::class, 'discardedProductQuantity'])->name('discard-product');
    Route::post('replace-discarded-product', [ProductController::class, 'replaceDiscardedProductsQuantity'])->name('replace-discarded-product');

    Route::prefix('calculate-')->name('calculate.')->group(function () {
        Route::get('sold-quantity', [CalculationController::class, 'calculateSoldProductsToday'])->name('calculate-sold-quantity');

    });

    Route::prefix('test/')->name('test.')->group(function () {
        Route::post('request', [CalculationController::class, 'testRequest'])->name('test-request');
        
    });

});

