<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestFormController;
use App\Http\Controllers\QuizController as ControllersQuizController;
use App\Http\Controllers\ZoomController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\SharedFormController;

// Guest Form Routes - Subdomain based (MUST be first!)
// URL format: {subdomain}.form.iadcsuez.org
Route::domain('{subdomain}.form.iadcsuez.org')->group(function () {
    Route::get('/', [GuestFormController::class, 'show'])->name('form.show');
    Route::post('/', [GuestFormController::class, 'submit'])->name('form.submit');
});

// Single global share route for generated image screens
Route::get('/s/{id}', [GuestFormController::class, 'sharePage'])->name('form.share');

Route::domain('iadcsuez.org')->group(function () {
    Route::get('/', [LandingPageController::class, 'index'])->name('landing');
});
Route::get('event/{id}', [LandingPageController::class, 'eventPreview'])->name('eventPreview');
Route::get('articles', [LandingPageController::class, 'articlesList'])->name('articlesList');
Route::get('article/{id}', [LandingPageController::class, 'articlePreview'])->name('articlePreview');
Route::get('magazine/{id}', [LandingPageController::class, 'magazineViewer'])->name('magazineViewer');
Route::post('contact', [ContactController::class, 'store'])->name('contact.store');
Route::post('newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('privacy-policy', [LandingPageController::class, 'privacyPolicy'])->name('privacy-policy');
// Main domain routes

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('index');

    // Shared Form Submissions Routes (Public)
    Route::get('/shared-forms/{encryptedId}/submissions', [SharedFormController::class, 'showSubmissions'])->name('shared-forms.submissions.show');
    Route::get('/shared-forms/{encryptedId}/submissions/export', [SharedFormController::class, 'exportSubmissions'])->name('shared-forms.submissions.export');
});

Route::get('/zoom/oauth', [ZoomController::class, 'oauth'])->name('zoom.oauth');
Route::get('/zoom/callback', [ZoomController::class, 'callback'])->name('zoom.callback');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

Route::get('/quiz/{id}', [ControllersQuizController::class, 'showQuiz'])->name('quiz.show');
Route::post('/quiz/{id}/finish', [ControllersQuizController::class, 'finishQuiz'])->name('quiz.finish');

// Unified Google Auth Routes
Route::get('auth/google', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

// Chunk Upload Route
Route::post('upload/chunk', [\App\Http\Controllers\ChunkUploadController::class, 'upload'])->name('upload.chunk');
