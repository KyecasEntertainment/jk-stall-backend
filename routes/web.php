<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;

Route::get('/', [UserController::class, 'test']);
Route::get('/welcome', [UserController::class, 'test'])->name('welcome');
// Route::post('/inventory/create-batch', [ProductController::class, 'createProduct'])->name('create-batch');


Route::prefix('admin/')->name('admin.')->group(function () {
    Route::get('login', [UserController::class, 'login'])->name('login');

});

Route::prefix('inventory/')->name('inventory.')->group(function () {
    //Route::post('create-batch', [ProductController::class, 'createProduct'])->name('create-product');
    Route::get('edit-product/{id}', [UserController::class, 'editProduct'])->name('edit-product');
    Route::put('update-product/{id}', [UserController::class, 'updateProduct'])->name('update-product');
});