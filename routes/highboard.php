<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\highboard\BoardController;
use App\Http\Controllers\highboard\CommitteeController;
use App\Http\Controllers\highboard\DashboardController;
use App\Http\Controllers\highboard\LessonController;
use App\Http\Controllers\highboard\MemberController;
use App\Http\Controllers\highboard\ProfileController;
use App\Http\Controllers\highboard\TaskController;
use App\Http\Controllers\highboard\SessionController;
use App\Http\Controllers\highboard\QuizController;
use App\Http\Controllers\highboard\QuestionController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:highboard')->group(function () {
    Route::get('/highboard/login', [LoginController::class, 'highboardLogin'])->name('highboard.login');
    Route::post('/highboard/login', [LoginController::class, 'highboardAuthenticate']);
});

Route::middleware(['auth:highboard', 'check.active:highboard'])->prefix('highboard')->name('highboard.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/logout', [LoginController::class, 'highboardlogout'])->name('logout');
    
    // Committee Management Routes
    Route::resource('committees', CommitteeController::class);
    Route::post('committees/{committee}/toggle-status', [CommitteeController::class, 'toggleStatus'])
        ->name('committees.toggle-status');
    
    // Board Management Routes
    Route::resource('boards', BoardController::class);
    Route::post('boards/{board}/toggle-status', [BoardController::class, 'toggleStatus'])
        ->name('boards.toggle-status');
    
    // Member Management Routes
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

    // Session Management
    Route::resource('sessions', SessionController::class);
    Route::get('sessions/{session}/join', [SessionController::class, 'join'])->name('sessions.join');
    
    // Highboard Impersonation Routes
    Route::post('login-as-board/{id}', [LoginController::class, 'highboardLoginAsBoard'])
        ->name('login-as-board');
    Route::post('login-as-member/{id}', [LoginController::class, 'highboardLoginAsMember'])
        ->name('login-as-member');
    

    // Quiz Routes
    Route::resource('quizzes', QuizController::class);
    Route::patch('quizzes/{quiz}/toggle-active', [QuizController::class, 'toggleActive'])->name('quizzes.toggle-active');
    Route::get('quizzes/{quiz}/leaderboard', [QuizController::class, 'leaderboard'])->name('quizzes.leaderboard');
    Route::get('quizzes/{quiz}/leaderboard/export', [QuizController::class, 'exportLeaderboard'])->name('quizzes.leaderboard.export');
    Route::delete('quizzes/{quiz}/leaderboard/clear', [QuizController::class, 'clearLeaderboard'])->name('quizzes.leaderboard.clear');
    Route::resource('quizzes.questions', QuestionController::class)->shallow();
    
    // Profile Routes
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
