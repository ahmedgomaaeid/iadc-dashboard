<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:user')->group(function () {
    Route::get('/login', [LoginController::class, 'userLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'userAuthenticate']);
});

Route::middleware('auth:user')->group(function () {
    Route::get('/dashboard', function () {
        return 'User Dashboard';
    })->name('user.dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});
