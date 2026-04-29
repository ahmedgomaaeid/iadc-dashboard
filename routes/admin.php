<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
    Route::middleware('guest:admin')->group(function () {
        Route::get('/admin/login', [LoginController::class, 'adminLogin'])->name('admin.login');
        Route::post('/admin/login', [LoginController::class, 'adminAuthenticate']);
    });

    Route::group(['middleware' => 'auth:admin', 'prefix' => 'admin', 'as' => 'admin.'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [LoginController::class, 'adminlogout'])->name('logout');

        // Field Management Routes
        Route::resource('fields', \App\Http\Controllers\Admin\FieldController::class);
        Route::post('fields/{field}/toggle-status', [\App\Http\Controllers\Admin\FieldController::class, 'toggleStatus'])
            ->name('fields.toggle-status');

        // Committee Management Routes
        Route::resource('committees', \App\Http\Controllers\Admin\CommitteeController::class);
        Route::post('committees/{committee}/toggle-status', [\App\Http\Controllers\Admin\CommitteeController::class, 'toggleStatus'])
            ->name('committees.toggle-status');

        // Highboard Management Routes
        Route::resource('highboards', \App\Http\Controllers\Admin\HighboardController::class);
        Route::post('highboards/{highboard}/toggle-status', [\App\Http\Controllers\Admin\HighboardController::class, 'toggleStatus'])
            ->name('highboards.toggle-status');

        // Board Management Routes
        Route::resource('boards', \App\Http\Controllers\Admin\BoardController::class);
        Route::post('boards/{board}/toggle-status', [\App\Http\Controllers\Admin\BoardController::class, 'toggleStatus'])
            ->name('boards.toggle-status');

        // Member Management Routes
        Route::resource('members', \App\Http\Controllers\Admin\MemberController::class);
        Route::post('members/{id}/toggle-status', [\App\Http\Controllers\Admin\MemberController::class, 'toggleStatus'])
            ->name('members.toggle-status');
        Route::post('members/bulk-status', [\App\Http\Controllers\Admin\MemberController::class, 'bulkStatus'])
            ->name('members.bulk-status');
        Route::get('members-export', [\App\Http\Controllers\Admin\MemberController::class, 'export'])
            ->name('members.export');

        // Quiz Management Routes
        Route::resource('quizzes', \App\Http\Controllers\Admin\QuizController::class);
        Route::patch('quizzes/{quiz}/toggle-active', [\App\Http\Controllers\Admin\QuizController::class, 'toggleActive'])
            ->name('quizzes.toggle-active');
        Route::get('quizzes/{quiz}/leaderboard', [\App\Http\Controllers\Admin\QuizController::class, 'leaderboard'])
            ->name('quizzes.leaderboard');
        Route::get('quizzes/{quiz}/leaderboard/export', [\App\Http\Controllers\Admin\QuizController::class, 'exportLeaderboard'])
            ->name('quizzes.leaderboard.export');
        Route::delete('quizzes/{quiz}/leaderboard/clear', [\App\Http\Controllers\Admin\QuizController::class, 'clearLeaderboard'])
            ->name('quizzes.leaderboard.clear');
        Route::post('quizzes/{quiz}/questions/ai-import', [\App\Http\Controllers\Admin\QuizController::class, 'storeQuestionsFromText'])
            ->name('quizzes.questions.ai-import');
        Route::resource('quizzes.questions', \App\Http\Controllers\Admin\QuestionController::class)->shallow();
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class)->only([]);

        // Interactive Quiz Management Routes
        Route::resource('interactive_quizzes', \App\Http\Controllers\Admin\InteractiveQuizController::class);
        Route::patch('interactive_quizzes/{interactive_quiz}/toggle-active', [\App\Http\Controllers\Admin\InteractiveQuizController::class, 'toggleActive'])
            ->name('interactive_quizzes.toggle-active');
        Route::get('interactive_quizzes/{interactive_quiz}/leaderboard', [\App\Http\Controllers\Admin\InteractiveQuizController::class, 'leaderboard'])
            ->name('interactive_quizzes.leaderboard');
        Route::delete('interactive_quizzes/{interactive_quiz}/leaderboard/clear', [\App\Http\Controllers\Admin\InteractiveQuizController::class, 'clearLeaderboard'])
            ->name('interactive_quizzes.leaderboard.clear');
        Route::get('interactive_quizzes/{interactive_quiz}/state', [\App\Http\Controllers\Admin\InteractiveQuizController::class, 'state'])
            ->name('interactive_quizzes.state');
        Route::post('interactive_quizzes/{interactive_quiz}/next-question', [\App\Http\Controllers\Admin\InteractiveQuizController::class, 'nextQuestion'])
            ->name('interactive_quizzes.next-question');

        // WellSharp Quiz Management Routes
        Route::resource('wellsharp_quizzes', \App\Http\Controllers\Admin\WellSharpQuizController::class);
        Route::patch('wellsharp_quizzes/{wellsharp_quiz}/toggle-active', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'toggleActive'])
            ->name('wellsharp_quizzes.toggle-active');
        Route::get('wellsharp_quizzes/{wellsharp_quiz}/control', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'controlPanel'])
            ->name('wellsharp_quizzes.control');
        Route::get('wellsharp_quizzes/{wellsharp_quiz}/state', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'state'])
            ->name('wellsharp_quizzes.state');
        Route::post('wellsharp_quizzes/{wellsharp_quiz}/next-question', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'nextQuestion'])
            ->name('wellsharp_quizzes.next-question');
        Route::post('wellsharp_quizzes/{wellsharp_quiz}/add-participant', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'addParticipant'])
            ->name('wellsharp_quizzes.add-participant');
        Route::delete('wellsharp_quizzes/{wellsharp_quiz}/remove-participant/{participantId}', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'removeParticipant'])
            ->name('wellsharp_quizzes.remove-participant');
        Route::post('wellsharp_quizzes/{wellsharp_quiz}/add-score', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'addScore'])
            ->name('wellsharp_quizzes.add-score');
        Route::delete('wellsharp_quizzes/{wellsharp_quiz}/clear', [\App\Http\Controllers\Admin\WellSharpQuizController::class, 'clearSession'])
            ->name('wellsharp_quizzes.clear');

        // Dynamic Form Management Routes
        Route::resource('dynamic-forms', \App\Http\Controllers\Admin\DynamicFormController::class);
        Route::patch('dynamic-forms/{dynamicForm}/toggle-active', [\App\Http\Controllers\Admin\DynamicFormController::class, 'toggleActive'])
            ->name('dynamic-forms.toggle-active');
        Route::post('dynamic-forms/submissions/{submission}/toggle-payment', [\App\Http\Controllers\Admin\DynamicFormController::class, 'togglePayment'])
            ->name('dynamic-forms.submissions.toggle-payment');
        Route::delete('dynamic-forms/submissions/{submission}', [\App\Http\Controllers\Admin\DynamicFormController::class, 'destroySubmission'])
            ->name('dynamic-forms.submissions.destroy');
        Route::get('dynamic-forms/{dynamicForm}/export', [\App\Http\Controllers\Admin\DynamicFormController::class, 'exportSubmissions'])
            ->name('dynamic-forms.export');

        // Event/Visit Management Routes
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::post('events/{id}/toggle-status', [\App\Http\Controllers\Admin\EventController::class, 'toggleStatus'])
            ->name('events.toggle-status');
        Route::delete('events/partners/{id}', [\App\Http\Controllers\Admin\EventController::class, 'destroyPartner'])
            ->name('events.partners.destroy');
        Route::post('events/partners/update-order', [\App\Http\Controllers\Admin\EventController::class, 'updatePartnerOrder'])
            ->name('events.partners.update-order');
        Route::delete('events/images/{id}', [\App\Http\Controllers\Admin\EventController::class, 'destroyImage'])
            ->name('events.images.destroy');
        Route::post('events/images/update-order', [\App\Http\Controllers\Admin\EventController::class, 'updateImageOrder'])
            ->name('events.images.update-order');

        // Article Management Routes
        Route::resource('articles', \App\Http\Controllers\Admin\ArticleController::class);
        Route::post('articles/{id}/toggle-status', [\App\Http\Controllers\Admin\ArticleController::class, 'toggleStatus'])
            ->name('articles.toggle-status');

        // Magazine Management Routes
        Route::resource('magazines', \App\Http\Controllers\Admin\MagazineController::class);
        Route::post('magazines/{id}/toggle-status', [\App\Http\Controllers\Admin\MagazineController::class, 'toggleStatus'])
            ->name('magazines.toggle-status');

        // Admin Impersonation Routes
        Route::post('login-as-highboard/{id}', [\App\Http\Controllers\Auth\LoginController::class, 'loginAsHighboard'])
            ->name('login-as-highboard');
        Route::post('login-as-board/{id}', [\App\Http\Controllers\Auth\LoginController::class, 'loginAsBoard'])
            ->name('login-as-board');

        // Contact Messages Routes
        Route::get('contact-messages', [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])
            ->name('contact-messages.index');
        Route::get('contact-messages/{id}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])
            ->name('contact-messages.show');
        Route::post('contact-messages/{id}/mark-read', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsRead'])
            ->name('contact-messages.mark-read');
        Route::post('contact-messages/{id}/mark-unread', [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsUnread'])
            ->name('contact-messages.mark-unread');
        Route::delete('contact-messages/{id}', [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])
            ->name('contact-messages.destroy');

        // Newsletter Subscribers Routes
        Route::get('newsletter-subscribers', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'index'])
            ->name('newsletter-subscribers.index');
        Route::post('newsletter-subscribers/{id}/toggle-status', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'toggleStatus'])
            ->name('newsletter-subscribers.toggle-status');
        Route::delete('newsletter-subscribers/{id}', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'destroy'])
            ->name('newsletter-subscribers.destroy');
        Route::get('newsletter-subscribers/export', [\App\Http\Controllers\Admin\NewsletterSubscriberController::class, 'export'])
            ->name('newsletter-subscribers.export');

        // Profile Routes
        Route::get('profile', [ProfileController::class, 'edit'])
            ->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])
            ->name('profile.password');
    });
});
