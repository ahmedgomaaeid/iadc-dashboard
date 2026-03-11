<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Supervisor\DashboardController;
use App\Http\Controllers\Supervisor\EmailController;
use App\Http\Controllers\Supervisor\ProfileController;
use App\Http\Controllers\Supervisor\SessionController;
use Illuminate\Support\Facades\Route;

$domain = parse_url(env('APP_URL'), PHP_URL_HOST) ?? env('APP_URL');

Route::domain($domain)->group(function () {
    Route::middleware('guest:supervisor')->group(function () {
        Route::get('/supervisor/login', [LoginController::class, 'supervisorLogin'])->name('supervisor.login');
        Route::post('/supervisor/login', [LoginController::class, 'supervisorAuthenticate']);
    });

    Route::group(['middleware' => 'auth:supervisor', 'prefix' => 'supervisor', 'as' => 'supervisor.'], function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/logout', [LoginController::class, 'supervisorlogout'])->name('logout');

        // Profile Routes
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        // Sessions Route
        Route::get('sessions', [SessionController::class, 'index'])->name('sessions.index');

        // Email Routes
        Route::get('email', [EmailController::class, 'inbox'])->name('email.inbox');
        Route::get('email/sent', [EmailController::class, 'sent'])->name('email.sent');
        Route::get('email/compose', [EmailController::class, 'compose'])->name('email.compose');
        Route::post('email/send', [EmailController::class, 'send'])->name('email.send');
        Route::get('email/{folder}/{uid}', [EmailController::class, 'show'])->name('email.show');
        Route::get('email/{folder}/{uid}/attachment/{partNumber}', [EmailController::class, 'downloadAttachment'])->name('email.attachment');
        Route::delete('email/{folder}/{uid}', [EmailController::class, 'destroy'])->name('email.destroy');
        Route::patch('email/{folder}/{uid}/toggle-read', [EmailController::class, 'toggleRead'])->name('email.toggleRead');
    });
});
