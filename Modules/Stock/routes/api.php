<?php

use Illuminate\Support\Facades\Route;
use Modules\Stock\Http\Controllers\StockController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/stock')->group(function () {
    Route::get('/', [StockController::class, 'index']);
    Route::get('movements', [StockController::class, 'movements']);
    Route::post('move', [StockController::class, 'move']);
    
    Route::get('locations', [StockController::class, 'locations']);
    Route::post('locations', [StockController::class, 'storeLocation']);
    
    Route::get('lots', [StockController::class, 'lots']);
    Route::post('lots', [StockController::class, 'storeLot']);
    
    Route::get('abc-curve', [StockController::class, 'abcCurve']);
});
