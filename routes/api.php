<?php

use Illuminate\Support\Facades\Route;
use App\Models\Quiz;
use App\Models\Question;
use App\Http\Controllers\Api\QuizController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| These routes are loaded by the Application configuration when the
| 'api' entry is registered in bootstrap/app.php via ->withRouting().
| They will automatically be prefixed with /api if you pass the api: path
| to withRouting(). Keep routes here stateless and return JSON responses.
*/


// Quiz details (including questions count)
Route::post('/quizzes/{quiz}/addParticipant', [QuizController::class, 'addParticipant']);
Route::get('/quizzes/{quiz}/getQuestion/{number}', [QuizController::class, 'getQuestion']);
Route::post('/quizzes/{quiz}/answerQuestion', [QuizController::class, 'answerQuestion']);

// Leaderboard endpoint
Route::get('/quizzes/{quiz}/leaderboard', [QuizController::class, 'getLeaderboard']);
