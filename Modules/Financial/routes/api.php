<?php

use Illuminate\Support\Facades\Route;
use Modules\Financial\Http\Controllers\FinancialController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/financial')->group(function () {
    Route::get('accounts', [FinancialController::class, 'accounts']);
    Route::post('accounts', [FinancialController::class, 'storeAccount']);
    Route::get('cost-centers', [FinancialController::class, 'costCenters']);
    Route::post('cost-centers', [FinancialController::class, 'storeCostCenter']);
    
    Route::get('receivables', [FinancialController::class, 'receivables']);
    Route::post('receivables', [FinancialController::class, 'storeReceivable']);
    Route::post('receivables/{id}/receive', [FinancialController::class, 'receivePayment']);
    
    Route::get('payables', [FinancialController::class, 'payables']);
    Route::post('payables', [FinancialController::class, 'storePayable']);
    Route::post('payables/{id}/pay', [FinancialController::class, 'payBill']);
    
    Route::get('cashflow', [FinancialController::class, 'cashFlow']);
    Route::get('dre', [FinancialController::class, 'dre']);
});
