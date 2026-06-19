<?php

use Illuminate\Support\Facades\Route;
use Modules\Production\Http\Controllers\ProductionController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/production')->group(function () {
    Route::get('boms', [ProductionController::class, 'boms']);
    Route::post('boms', [ProductionController::class, 'storeBom']);
    Route::get('orders', [ProductionController::class, 'orders']);
    Route::post('orders', [ProductionController::class, 'storeOrder']);
    Route::post('orders/{id}/start', [ProductionController::class, 'startOrder']);
    Route::post('orders/{id}/complete', [ProductionController::class, 'completeOrder']);
});
