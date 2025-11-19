<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:board')->group(function () {
    Route::get('/board/login', [LoginController::class, 'boardLogin'])->name('board.login');
    Route::post('/board/login', [LoginController::class, 'boardAuthenticate']);
});

Route::middleware('auth:board')->group(function () {
    Route::get('/board', function () {
        return 'Board Dashboard';
    })->name('board.dashboard');
    Route::post('/board/logout', [LoginController::class, 'logout'])->name('board.logout');
});
