<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ---- Public routes ----

// Customer Auth
Route::prefix('v1/auth')->group(function () {
    Route::post('/customer/login', [CustomerAuthController::class, 'login']);
});

// Products (public catalog)
Route::prefix('v1')->group(function () {
    Route::get('/products', [\Modules\Products\Http\Controllers\ProductsController::class, 'index']);
    Route::get('/products/{id}', [\Modules\Products\Http\Controllers\ProductsController::class, 'show']);
});

// Checkout (guest)
Route::post('/v1/checkout', [\Modules\Orders\Http\Controllers\CheckoutController::class, 'store']);


// ---- Protected customer routes (Sanctum) ----
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Customer profile
    Route::get('/customer/me', [CustomerAuthController::class, 'me']);
    Route::post('/customer/logout', [CustomerAuthController::class, 'logout']);

    // Customer orders
    Route::get('/customer/orders', [\Modules\Orders\Http\Controllers\OrdersController::class, 'index']);
    Route::get('/customer/orders/{id}', [\Modules\Orders\Http\Controllers\OrdersController::class, 'show']);
});

// ---- Protected ERP routes (web auth) ----
Route::middleware('auth:sanctum')->prefix('v1/erp')->group(function () {
    // Products management
    Route::post('/products', [\Modules\Products\Http\Controllers\ProductsController::class, 'store']);
    Route::put('/products/{id}', [\Modules\Products\Http\Controllers\ProductsController::class, 'update']);
    Route::delete('/products/{id}', [\Modules\Products\Http\Controllers\ProductsController::class, 'destroy']);
});
