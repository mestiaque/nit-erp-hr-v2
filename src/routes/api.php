<?php
use Illuminate\Support\Facades\Route;
use ME\Hr\Http\Controllers\HrController;
use ME\Hr\Http\Controllers\api\EmployeeApiController;

Route::prefix('api/hr')->group(function() {
    Route::get('/', [HrController::class, 'index']);
    Route::get('/employees', [EmployeeApiController::class, 'index']);
});