<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/', [UserController::class, 'test']);


Route::prefix('admin/')->name('admin.')->group(function () {
    Route::get('login', [UserController::class, 'login'])->name('login');

});

Route::prefix('inventory/')->name('inventory.')->group(function () {
    Route::post('create-product', [UserController::class, 'createProduct'])->name('create-product');
    Route::get('view-products', [UserController::class, 'viewProducts'])->name('view-products');
    Route::get('edit-product/{id}', [UserController::class, 'editProduct'])->name('edit-product');
    Route::put('update-product/{id}', [UserController::class, 'updateProduct'])->name('update-product');
});