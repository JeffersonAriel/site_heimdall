<?php

use Illuminate\Support\Facades\Route;
use Modules\Fiscal\Http\Controllers\FiscalController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/fiscal')->group(function () {
    Route::get('invoices', [FiscalController::class, 'index']);
    Route::post('invoices/issue', [FiscalController::class, 'issue']);
    Route::post('invoices/{id}/cancel', [FiscalController::class, 'cancel']);
    Route::get('invoices/order/{orderId}', [FiscalController::class, 'showByOrder']);
});
