<?php

use Illuminate\Support\Facades\Route;
use Modules\Fiscal\Http\Controllers\FiscalController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('fiscals', FiscalController::class)->names('fiscal');
});
