<?php

use Illuminate\Support\Facades\Route;
use Modules\BI\Http\Controllers\BIController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/bi')->group(function () {
    Route::get('kpis', [BIController::class, 'kpis']);
    Route::get('top-products', [BIController::class, 'topProducts']);
    Route::get('revenue-period', [BIController::class, 'revenuePeriod']);
});
