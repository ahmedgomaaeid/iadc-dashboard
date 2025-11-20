<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:highboard')->group(function () {
    Route::get('/highboard/login', [LoginController::class, 'highboardLogin'])->name('highboard.login');
    Route::post('/highboard/login', [LoginController::class, 'highboardAuthenticate']);
});

Route::middleware('auth:highboard')->group(function () {
    Route::get('/highboard', [\App\Http\Controllers\highboard\DashboardController::class, 'index'])->name('highboard.dashboard');
    Route::get('/highboard/logout', [LoginController::class, 'highboardlogout'])->name('highboard.logout');
    
    // Committee Management Routes
    Route::resource('highboard/committees', \App\Http\Controllers\highboard\CommitteeController::class, ['as' => 'highboard']);
    Route::post('highboard/committees/{committee}/toggle-status', [\App\Http\Controllers\highboard\CommitteeController::class, 'toggleStatus'])
        ->name('highboard.committees.toggle-status');
    
    // Board Management Routes
    Route::resource('highboard/boards', \App\Http\Controllers\highboard\BoardController::class, ['as' => 'highboard']);
    Route::post('highboard/boards/{board}/toggle-status', [\App\Http\Controllers\highboard\BoardController::class, 'toggleStatus'])
        ->name('highboard.boards.toggle-status');
    
    // Member Management Routes
    Route::resource('highboard/members', \App\Http\Controllers\highboard\MemberController::class, ['as' => 'highboard']);
    Route::post('highboard/members/{member}/toggle-status', [\App\Http\Controllers\highboard\MemberController::class, 'toggleStatus'])
        ->name('highboard.members.toggle-status');
    
    // Highboard Impersonation Routes
    Route::post('highboard/login-as-board/{id}', [\App\Http\Controllers\Auth\LoginController::class, 'highboardLoginAsBoard'])
        ->name('highboard.login-as-board');
    Route::post('highboard/login-as-member/{id}', [\App\Http\Controllers\Auth\LoginController::class, 'highboardLoginAsMember'])
        ->name('highboard.login-as-member');
    
    // Profile Routes
    Route::get('highboard/profile', [\App\Http\Controllers\highboard\ProfileController::class, 'edit'])
        ->name('highboard.profile.edit');
    Route::put('highboard/profile', [\App\Http\Controllers\highboard\ProfileController::class, 'update'])
        ->name('highboard.profile.update');
    Route::put('highboard/profile/password', [\App\Http\Controllers\highboard\ProfileController::class, 'updatePassword'])
        ->name('highboard.profile.password');
});
