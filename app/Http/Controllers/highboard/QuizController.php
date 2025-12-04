<?php

namespace App\Http\Controllers\highboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QuizCacheService;
use App\Exports\LeaderboardExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Quiz;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::paginate(15);
        return view('highboard.quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('highboard.quizzes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        Quiz::create($request->all());

        // return to view of this quiz to add questions
        return redirect()->route('highboard.quizzes.show', Quiz::latest()->first())->with('success', 'Quiz created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        return view('highboard.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        return view('highboard.quizzes.edit', compact('quiz'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        $quiz->update($request->all());

        return redirect()->route('highboard.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('highboard.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Toggle the active status of a quiz.
     */
    public function toggleActive(Quiz $quiz)
    {
        $quiz->is_active = ! (bool) $quiz->is_active;
        $quiz->save();

        if ($quiz->is_active) {
            // Cache questions to Redis when activating
            QuizCacheService::store($quiz); // also sets active flag
        } else {
            // Remove questions from Redis when deactivating
            QuizCacheService::clear($quiz); // also clears active flag
        }

        return back()->with('success', 'Quiz status updated.');
    }

    /**
     * Show leaderboard for a specific quiz.
     */
    public function leaderboard(Quiz $quiz)
    {
        return view('highboard.quizzes.leaderboard', compact('quiz'));
    }

    /**
     * Export leaderboard to Excel
     */
    public function exportLeaderboard(Quiz $quiz)
    {
        $filename = 'leaderboard_' . str_replace(' ', '_', $quiz->name) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new LeaderboardExport($quiz->id, $quiz->name), $filename);
    }

    /**
     * Clear leaderboard data from cache
     */
    public function clearLeaderboard(Quiz $quiz)
    {
        $participantsCleared = QuizCacheService::clearLeaderboard($quiz->id);

        if ($participantsCleared > 0) {
            return back()->with('success', "Leaderboard cleared successfully. {$participantsCleared} participant(s) removed.");
        } else {
            return back()->with('info', 'No leaderboard data found to clear.');
        }
    }
}
