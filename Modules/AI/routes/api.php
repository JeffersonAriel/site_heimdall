<?php

use Illuminate\Support\Facades\Route;
use Modules\AI\Http\Controllers\AIController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/ai')->group(function () {
    Route::get('logs', [AIController::class, 'logs']);
    Route::post('generate-description', [AIController::class, 'generateDescription']);
    Route::get('config', [AIController::class, 'config']);
    Route::post('config', [AIController::class, 'updateConfig']);
});
