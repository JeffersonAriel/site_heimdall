<?php

use Illuminate\Support\Facades\Route;
use Modules\HelpDesk\Http\Controllers\HelpDeskController;

// Internal ERP HelpDesk routes
Route::middleware(['auth:sanctum'])->prefix('v1/erp/helpdesk')->group(function () {
    Route::get('tickets', [HelpDeskController::class, 'index']);
    Route::get('tickets/{id}', [HelpDeskController::class, 'show']);
    Route::post('tickets/{id}/reply', [HelpDeskController::class, 'reply']);
});

// Public Customer HelpDesk routes
Route::middleware(['auth:sanctum'])->prefix('v1/customer/helpdesk')->group(function () {
    Route::get('tickets', [HelpDeskController::class, 'customerTickets']);
    Route::post('tickets', [HelpDeskController::class, 'store']);
    Route::get('tickets/{id}', [HelpDeskController::class, 'show']);
    Route::post('tickets/{id}/reply', [HelpDeskController::class, 'customerReply']);
});
