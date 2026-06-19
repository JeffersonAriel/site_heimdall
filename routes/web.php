<?php

use Illuminate\Support\Facades\Route;

// This will catch all web routes and pass them to Vue Router
Route::get('/{any}', function () {
    return view('welcome');
})->where('any', '.*');
