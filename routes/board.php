<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\board\DashboardController;
use App\Http\Controllers\board\LessonController;
use App\Http\Controllers\board\MemberController;
use App\Http\Controllers\board\ProfileController;
use App\Http\Controllers\board\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:board')->group(function () {
    Route::get('/board/login', [LoginController::class, 'boardLogin'])->name('board.login');
    Route::post('/board/login', [LoginController::class, 'boardAuthenticate']);
});

Route::middleware('auth:board')->prefix('board')->name('board.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Member Management  
    Route::resource('members', MemberController::class);
    Route::post('members/{member}/toggle-status', [MemberController::class, 'toggleStatus'])
        ->name('members.toggle-status');

    // Lesson Management
    Route::resource('lessons', LessonController::class);
    Route::delete('lessons/attachments/{attachment}', [LessonController::class, 'destroyAttachment'])
        ->name('lessons.attachments.destroy');

    // Task Management
    Route::resource('tasks', TaskController::class);
    Route::delete('tasks/attachments/{attachment}', [TaskController::class, 'destroyAttachment'])
        ->name('tasks.attachments.destroy');
    
    // Profile Management
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    
    // Logout
    Route::get('/logout', [LoginController::class, 'boardlogout'])->name('logout');
});
