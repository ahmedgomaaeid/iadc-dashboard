<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\user\DashboardController;
use App\Http\Controllers\user\LessonController;
use App\Http\Controllers\user\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:user')->group(function () {
    Route::get('/login', [LoginController::class, 'userLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'userAuthenticate']);
});

Route::middleware('auth:user')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');
    
    // Lessons
    Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
    Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

    // Tasks/Quizzes
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/submit', [TaskController::class, 'submit'])->name('tasks.submit');

    Route::get('/logout', [LoginController::class, 'userlogout'])->name('logout');
});
