<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\QuizCacheService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a list of private quizzes accessible by the authenticated user
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get user's committee IDs
        $userCommitteeIds = $user->committees->pluck('id')->toArray();
        
        // Get only private, active quizzes from user's committees
        $allQuizzes = Quiz::where('is_active', true)
            ->where('visibility', 'private')
            ->whereIn('committee_id', $userCommitteeIds)
            ->with('committee')
            ->latest()
            ->get();
        
        // Filter out quizzes the user has already participated in
        $quizzes = $allQuizzes->filter(function ($quiz) use ($user) {
            return !QuizCacheService::hasEmailParticipated($quiz->id, $user->email);
        });
        
        return view('user.quizzes.index', compact('quizzes'));
    }
}
