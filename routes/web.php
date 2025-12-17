<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestFormController;
use App\Http\Controllers\QuizController as ControllersQuizController;
use App\Http\Controllers\ZoomController;

// Guest Form Routes - Subdomain based (MUST be first!)
// URL format: {subdomain}.form.iadcsuez.org
Route::domain('{subdomain}.form.iadcsuez.org')->group(function () {
    Route::get('/', [GuestFormController::class, 'show'])->name('form.show');
    Route::post('/', [GuestFormController::class, 'submit'])->name('form.submit');
});
Route::domain('iadcsuez.org')->group(function () {
    Route::get('/', function () {
        return view('welcome');
    })->name('landing');
});

// Main domain routes

Route::domain(env('APP_URL'))->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    })->name('index');
});

Route::get('/zoom/oauth', [ZoomController::class, 'oauth'])->name('zoom.oauth');
Route::get('/zoom/callback', [ZoomController::class, 'callback'])->name('zoom.callback');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');

Route::get('/quiz/{id}', [ControllersQuizController::class, 'showQuiz'])->name('quiz.show');
