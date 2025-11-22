<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:board')->group(function () {
    Route::get('/board/login', [LoginController::class, 'boardLogin'])->name('board.login');
    Route::post('/board/login', [LoginController::class, 'boardAuthenticate']);
});

Route::middleware('auth:board')->group(function () {
    // Dashboard
    Route::get('/board', [\App\Http\Controllers\board\DashboardController::class, 'index'])->name('board.dashboard');
    
    // Member Management Routes
    Route::resource('board/members', \App\Http\Controllers\board\MemberController::class, ['as' => 'board']);
    Route::post('board/members/{member}/toggle-status', [\App\Http\Controllers\board\MemberController::class, 'toggleStatus'])
        ->name('board.members.toggle-status');
    
    // Profile Routes
    Route::get('board/profile', [\App\Http\Controllers\board\ProfileController::class, 'edit'])
        ->name('board.profile.edit');
    Route::put('board/profile', [\App\Http\Controllers\board\ProfileController::class, 'update'])
        ->name('board.profile.update');
    Route::put('board/profile/password', [\App\Http\Controllers\board\ProfileController::class, 'updatePassword'])
        ->name('board.profile.password');
    
    // Logout
    Route::get('/board/logout', [LoginController::class, 'boardlogout'])->name('board.logout');
});
