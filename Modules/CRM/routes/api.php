<?php

use Illuminate\Support\Facades\Route;
use Modules\CRM\Http\Controllers\CRMController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/crm')->group(function () {
    Route::get('pipeline', [CRMController::class, 'pipeline']);
    Route::post('pipeline/move/{id}', [CRMController::class, 'updateStage']);
    Route::get('activities', [CRMController::class, 'activities']);
    Route::post('activities', [CRMController::class, 'storeActivity']);
});
