<?php
use Illuminate\Support\Facades\Route;
use ME\Hr\Http\Controllers\HrController;

Route::prefix('api/hr')->group(function() {
    Route::get('/', [HrController::class, 'index']);
});