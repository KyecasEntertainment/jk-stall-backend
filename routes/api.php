<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

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
        Route::post('update/{id}', [ProductController::class, 'updateBatch'])->name('update-batch');
    });

    Route::get('view-products', [ProductController::class, 'viewProductsQuantity'])->name('view-products');

    Route::post('display-product', [ProductController::class, 'displayProductsQuantity'])->name('display-product');
    Route::post('return-product', [ProductController::class, 'returnProductsQuantity'])->name('back-product');
    Route::get('calculate-sold-quantity', [ProductController::class, 'calculateSoldProductsToday'])->name('calculate-sold-quantity');
    
    Route::get('edit-product/{id}', [ProductController::class, 'editProduct'])->name('edit-product');
    Route::put('update-product/{id}', [ProductController::class, 'updateProduct'])->name('update-product');
});

