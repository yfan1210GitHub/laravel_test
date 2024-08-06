<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\CompanyController;

Auth::routes();
Route::group(['prefix' => 'employee_management', 'as' => 'employee_management.'], function () {
    Route::apiResource('employee', EmployeeController::class);
});

Route::group(['prefix' => 'company_management', 'as' => 'company_management.'], function () {
    Route::apiResource('company', CompanyController::class);
});

