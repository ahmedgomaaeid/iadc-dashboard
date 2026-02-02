<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Board\DashboardController;
use App\Http\Controllers\Board\LessonController;
use App\Http\Controllers\Board\MemberController;
use App\Http\Controllers\Board\ProfileController;
use App\Http\Controllers\Board\TaskController;
use App\Http\Controllers\Board\SessionController;
use Illuminate\Support\Facades\Route;

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
    Route::middleware('guest:board')->group(function () {
        Route::get('/board/login', [LoginController::class, 'boardLogin'])->name('board.login');
        Route::post('/board/login', [LoginController::class, 'boardAuthenticate']);
    });

    Route::middleware(['auth:board', 'check.active:board'])->prefix('board')->name('board.')->group(function () {
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

        // Task Submission Management
        Route::get('tasks-submissions', [TaskController::class, 'submissions'])
            ->name('tasks.submissions');
        Route::get('tasks-submissions/{submission}', [TaskController::class, 'showSubmission'])
            ->name('tasks.submissions.show');
        Route::post('tasks-submissions/{submission}/accept', [TaskController::class, 'acceptSubmission'])
            ->name('tasks.submissions.accept');
        Route::post('tasks-submissions/{submission}/reject', [TaskController::class, 'rejectSubmission'])
            ->name('tasks.submissions.reject');

        // Quiz Management
        Route::resource('quizzes', \App\Http\Controllers\Board\QuizController::class);
        Route::patch('quizzes/{quiz}/toggle-active', [\App\Http\Controllers\Board\QuizController::class, 'toggleActive'])
            ->name('quizzes.toggle-active');
        Route::get('quizzes/{quiz}/leaderboard', [\App\Http\Controllers\Board\QuizController::class, 'leaderboard'])
            ->name('quizzes.leaderboard');
        Route::get('quizzes/{quiz}/leaderboard/export', [\App\Http\Controllers\Board\QuizController::class, 'exportLeaderboard'])
            ->name('quizzes.leaderboard.export');
        Route::delete('quizzes/{quiz}/leaderboard/clear', [\App\Http\Controllers\Board\QuizController::class, 'clearLeaderboard'])
            ->name('quizzes.leaderboard.clear');
        Route::post('quizzes/{quiz}/questions/ai-import', [\App\Http\Controllers\Board\QuizController::class, 'storeQuestionsFromText'])
            ->name('quizzes.questions.ai-import');
        Route::resource('quizzes.questions', \App\Http\Controllers\Board\QuestionController::class)->shallow();
        Route::resource('questions', \App\Http\Controllers\Board\QuestionController::class)->only([]);

        // Session Management
        // Route::resource('sessions', SessionController::class);
        // Route::get('sessions/{session}/join', [SessionController::class, 'join'])->name('sessions.join');

        // Profile Management
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Logout
        Route::get('/logout', [LoginController::class, 'boardlogout'])->name('logout');
    });
});
