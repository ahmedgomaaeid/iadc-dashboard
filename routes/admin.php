<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login');
    Route::post('/admin/login', [LoginController::class, 'adminAuthenticate']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin', function () {
        return 'Admin Dashboard';
    })->name('admin.dashboard');
    Route::get('/admin/logout', [LoginController::class, 'logout'])->name('admin.logout');
});
