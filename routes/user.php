<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController;
use App\Http\Controllers\User\LessonController;
use App\Http\Controllers\User\TaskController;
use App\Http\Controllers\User\SessionController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\User\QuizController;
use Illuminate\Support\Facades\Route;


    Route::middleware('guest:user')->group(function () {
        Route::get('/login', [LoginController::class, 'userLogin'])->name('login');
        Route::post('/login', [LoginController::class, 'userAuthenticate']);
    });

    Route::middleware(['auth:user', 'check.active:user'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('user.dashboard');

        // Lessons
        Route::get('/lessons', [LessonController::class, 'index'])->name('lessons.index');
        Route::get('/lessons/{lesson}', [LessonController::class, 'show'])->name('lessons.show');

        // Tasks/Quizes
        Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
        Route::get('/tasks/{task}', [TaskController::class, 'show'])->name('tasks.show');
        Route::post('/tasks/{task}/submit', [TaskController::class, 'submit'])->name('tasks.submit');

        // Quizzes
        Route::get('/quizzes', [QuizController::class, 'index'])->name('user.quizzes.index');

        // Sessions
        Route::get('/sessions', [SessionController::class, 'index'])->name('user.sessions.index');
        Route::get('/sessions/{googleSession}/join', [SessionController::class, 'join'])->name('user.sessions.join');
        Route::get('/sessions/{googleSession}/evaluate', [SessionController::class, 'evaluate'])->name('user.sessions.evaluate');
        Route::post('/sessions/{googleSession}/evaluate', [SessionController::class, 'storeEvaluation'])->name('user.sessions.evaluate.store');

        // Profile Management
        Route::get('/profile', [ProfileController::class, 'edit'])->name('user.profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('user.profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('user.profile.password');

        Route::get('/logout', [LoginController::class, 'userlogout'])->name('logout');
    });