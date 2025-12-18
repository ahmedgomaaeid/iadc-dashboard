<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;
Route::domain(env('APP_URL'))->group(function () {
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
        Route::resource('quizzes.questions', \App\Http\Controllers\Admin\QuestionController::class)->shallow();
        Route::resource('questions', \App\Http\Controllers\Admin\QuestionController::class)->only([]);

        // Dynamic Form Management Routes
        Route::resource('dynamic-forms', \App\Http\Controllers\Admin\DynamicFormController::class);
        Route::patch('dynamic-forms/{dynamicForm}/toggle-active', [\App\Http\Controllers\Admin\DynamicFormController::class, 'toggleActive'])
            ->name('dynamic-forms.toggle-active');
        Route::get('dynamic-forms/{dynamicForm}/export', [\App\Http\Controllers\Admin\DynamicFormController::class, 'exportSubmissions'])
            ->name('dynamic-forms.export');

        // Event/Visit Management Routes
        Route::resource('events', \App\Http\Controllers\Admin\EventController::class);
        Route::post('events/{id}/toggle-status', [\App\Http\Controllers\Admin\EventController::class, 'toggleStatus'])
            ->name('events.toggle-status');
        Route::delete('events/partners/{id}', [\App\Http\Controllers\Admin\EventController::class, 'destroyPartner'])
            ->name('events.partners.destroy');

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
