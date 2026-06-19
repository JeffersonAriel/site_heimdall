<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;
use Modules\Orders\Http\Controllers\CheckoutController;
use Modules\Orders\Http\Controllers\EcommerceController;

// Public storefront routes (can also be auth/guest hybrid)
Route::prefix('v1')->group(function () {
    Route::post('checkout', [CheckoutController::class, 'store']);
    Route::get('products/{productId}/reviews', [EcommerceController::class, 'reviews']);
    Route::post('products/{productId}/reviews', [EcommerceController::class, 'storeReview']);
    Route::get('blog', [EcommerceController::class, 'blog']);
    Route::get('blog/{slug}', [EcommerceController::class, 'blogPost']);
    Route::post('blog', [EcommerceController::class, 'storeBlogPost']);
});

// Protected customer routes
Route::middleware(['auth:sanctum'])->prefix('v1/customer')->group(function () {
    Route::get('wishlist', [EcommerceController::class, 'wishlist']);
    Route::post('wishlist', [EcommerceController::class, 'addToWishlist']);
    Route::delete('wishlist/{productId}', [EcommerceController::class, 'removeFromWishlist']);
});

// ERP Admin routes
Route::middleware(['auth:sanctum'])->prefix('v1/erp')->group(function () {
    Route::get('orders', [OrdersController::class, 'index']);
    Route::get('coupons', [EcommerceController::class, 'listCoupons']);
    Route::post('coupons', [EcommerceController::class, 'storeCoupon']);
});
