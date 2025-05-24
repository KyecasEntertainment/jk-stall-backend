<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::prefix('inventory/')->name('inventory.')->group(function () {
    Route::post('create-product', [ProductController::class, 'createProduct'])->name('create-product');
    Route::get('view-products', [ProductController::class, 'viewProducts'])->name('view-products');
    Route::get('edit-product/{id}', [ProductController::class, 'editProduct'])->name('edit-product');
    Route::put('update-product/{id}', [ProductController::class, 'updateProduct'])->name('update-product');
});
