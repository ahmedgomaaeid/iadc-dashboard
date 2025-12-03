<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('index');

use App\Http\Controllers\ZoomController;
Route::get('/zoom/oauth', [ZoomController::class, 'oauth'])->name('zoom.oauth');
Route::get('/zoom/callback', [ZoomController::class, 'callback'])->name('zoom.callback');
Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->name('register.post');
