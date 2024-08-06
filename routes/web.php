<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\CompanyController;

// Auth::routes();
Route::get('/index', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/test', function () {
    return 'Test route is working!';
});
Route::get('/debug', function () {
    return 'Debug route is working!';
});

// Route::group(['prefix' => 'employee_management', 'as' => 'employee_management.'], function () {
//     Route::apiResource('employee', EmployeeController::class);
// });

// Route::group(['prefix' => 'company_management', 'as' => 'company_management.'], function () {
//     Route::apiResource('company', CompanyController::class);
// });

