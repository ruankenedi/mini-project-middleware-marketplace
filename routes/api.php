<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SyncController;

Route::post('login', [AuthController::class, 'login']);

// routes protected
Route::middleware(['jwt.auth'])->group(function () {
    Route::get('suppliers', [SupplierController::class, 'index']);
    Route::post('suppliers/{supplier}/sync', [SyncController::class, 'syncSupplier']);
    Route::get('products', [ProductController::class, 'index']);
});
