<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Highboard\BoardController;
use App\Http\Controllers\Highboard\CommitteeController;
use App\Http\Controllers\Highboard\DashboardController;
use App\Http\Controllers\Highboard\EvaluationController;
use App\Http\Controllers\Highboard\UserEvaluationController;
use App\Http\Controllers\Highboard\LessonController;
use App\Http\Controllers\Highboard\MemberController;
use App\Http\Controllers\Highboard\ProfileController;
use App\Http\Controllers\Highboard\TaskController;
use App\Http\Controllers\Highboard\SessionController;
use App\Http\Controllers\Highboard\GoogleSessionController;
use App\Http\Controllers\Highboard\QuizController;
use App\Http\Controllers\Highboard\QuestionController;
use App\Http\Controllers\Highboard\DynamicFormController;
use Illuminate\Support\Facades\Route;

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
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
        Route::get('members/export', [MemberController::class, 'export'])->name('members.export');
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


        // Session Management
        Route::resource('sessions', GoogleSessionController::class);
        Route::get('sessions/{googleSession}/join', [GoogleSessionController::class, 'join'])->name('sessions.join');

        // Evaluation Routes
        Route::get('users/evaluations', [UserEvaluationController::class, 'index'])->name('users.evaluations.index');
        Route::get('evaluations', [EvaluationController::class, 'index'])->name('evaluations.index');
        Route::get('evaluations/participation', [EvaluationController::class, 'participation'])->name('evaluations.participation');
        Route::post('evaluations/participation', [EvaluationController::class, 'storeParticipation'])->name('evaluations.participation.store');
        Route::get('evaluations/interaction/{googleSession}', [EvaluationController::class, 'interaction'])->name('evaluations.interaction');
        Route::post('evaluations/interaction/{googleSession}', [EvaluationController::class, 'storeInteraction'])->name('evaluations.interaction.store');
        Route::get('evaluations/participants/{googleSession}', [EvaluationController::class, 'getParticipants'])->name('evaluations.participants');
        
        // Session Instructor Evaluation Routes
        Route::get('evaluations/sessions', [EvaluationController::class, 'instructorEvaluationsIndex'])->name('evaluations.sessions.index');
        Route::get('evaluations/sessions/{googleSession}', [EvaluationController::class, 'showSessionInstructorEvaluations'])->name('evaluations.sessions.show');
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
        Route::post('quizzes/{quiz}/questions/ai-import', [QuizController::class, 'storeQuestionsFromText'])->name('quizzes.questions.ai-import');
        Route::resource('quizzes.questions', QuestionController::class)->shallow();

        // Interactive Quiz Routes
        Route::resource('interactive_quizzes', \App\Http\Controllers\Highboard\InteractiveQuizController::class)->parameters(['interactive_quizzes' => 'interactive_quiz']);
        Route::patch('interactive_quizzes/{interactive_quiz}/toggle-active', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'toggleActive'])->name('interactive_quizzes.toggle-active');
        Route::get('interactive_quizzes/{interactive_quiz}/leaderboard', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'leaderboard'])->name('interactive_quizzes.leaderboard');
        Route::get('interactive_quizzes/{interactive_quiz}/leaderboard/export', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'exportLeaderboard'])->name('interactive_quizzes.leaderboard.export');
        Route::delete('interactive_quizzes/{interactive_quiz}/leaderboard/clear', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'clearLeaderboard'])->name('interactive_quizzes.leaderboard.clear');
        Route::get('interactive_quizzes/{interactive_quiz}/state', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'state'])->name('interactive_quizzes.state');
        Route::post('interactive_quizzes/{interactive_quiz}/next-question', [\App\Http\Controllers\Highboard\InteractiveQuizController::class, 'nextQuestion'])->name('interactive_quizzes.next-question');

        // Dynamic Form Routes
        Route::resource('dynamic-forms', DynamicFormController::class)->only(['index', 'show']);
        Route::post('dynamic-forms/submissions/{submission}/toggle-payment', [DynamicFormController::class, 'togglePayment'])->name('dynamic-forms.submissions.toggle-payment');
        Route::delete('dynamic-forms/submissions/{submission}', [DynamicFormController::class, 'destroySubmission'])->name('dynamic-forms.submissions.destroy');
        Route::get('dynamic-forms/{dynamicForm}/export', [DynamicFormController::class, 'exportSubmissions'])->name('dynamic-forms.export');

        // Profile Routes
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    });
});
