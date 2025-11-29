<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
})->name('index');

use App\Http\Controllers\ZoomController;
Route::get('/zoom/oauth', [ZoomController::class, 'oauth'])->name('zoom.oauth');
Route::get('/zoom/callback', [ZoomController::class, 'callback'])->name('zoom.callback');
