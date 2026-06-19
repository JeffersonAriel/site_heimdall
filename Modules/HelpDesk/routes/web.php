<?php

use Illuminate\Support\Facades\Route;
use Modules\HelpDesk\Http\Controllers\HelpDeskController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('helpdesks', HelpDeskController::class)->names('helpdesk');
});
