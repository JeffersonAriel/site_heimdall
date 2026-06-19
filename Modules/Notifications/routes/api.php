<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\NotificationsController;

Route::middleware(['auth:sanctum'])->prefix('v1/erp/notifications')->group(function () {
    Route::get('/', [NotificationsController::class, 'index']);
    Route::post('{id}/read', [NotificationsController::class, 'read']);
});
