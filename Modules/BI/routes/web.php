<?php

use Illuminate\Support\Facades\Route;
use Modules\BI\Http\Controllers\BIController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('bis', BIController::class)->names('bi');
});
