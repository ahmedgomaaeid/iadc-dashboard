<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:highboard')->group(function () {
    Route::get('/highboard/login', [LoginController::class, 'highboardLogin'])->name('highboard.login');
    Route::post('/highboard/login', [LoginController::class, 'highboardAuthenticate']);
});

Route::middleware('auth:highboard')->group(function () {
    Route::get('/highboard', function () {
        return 'Highboard Dashboard';
    })->name('highboard.dashboard');
    Route::get('/highboard/logout', [LoginController::class, 'highboardlogout'])->name('highboard.logout');
});
