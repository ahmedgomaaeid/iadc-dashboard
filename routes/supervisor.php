<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Supervisor\DashboardController;
use App\Http\Controllers\Supervisor\ProfileController;
use Illuminate\Support\Facades\Route;

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
    Route::middleware('guest:supervisor')->group(function () {
        Route::get('/supervisor/login', [LoginController::class, 'supervisorLogin'])->name('supervisor.login');
        Route::post('/supervisor/login', [LoginController::class, 'supervisorAuthenticate']);
    });

    Route::group(['middleware' => 'auth:supervisor', 'prefix' => 'supervisor', 'as' => 'supervisor.'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [LoginController::class, 'supervisorlogout'])->name('logout');

        // Profile Routes
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
