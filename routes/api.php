<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::prefix('inventory/')->name('inventory.')->group(function () {

    Route::prefix('products/')->name('products.')->group(function () {
        Route::post('create', [ProductController::class, 'createProduct'])->name('create-product');
        Route::get('view', [ProductController::class, 'viewProducts'])->name('view-products');
        Route::put('update/{id}', [ProductController::class, 'editProduct'])->name('update-product');
    });


    Route::post('create-batch', [ProductController::class, 'createBatch'])->name('create-product');
    Route::get('view-products', [ProductController::class, 'viewProductsQuantity'])->name('view-products');

    Route::post('display-product', [ProductController::class, 'displayProductsQuantity'])->name('display-product');
    Route::post('back-product', [ProductController::class, 'backProductsQuantity'])->name('back-product');
    
    Route::get('edit-product/{id}', [ProductController::class, 'editProduct'])->name('edit-product');
    Route::put('update-product/{id}', [ProductController::class, 'updateProduct'])->name('update-product');
});

