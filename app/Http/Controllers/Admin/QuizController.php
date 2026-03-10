<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\QuizCacheService;
use App\Exports\LeaderboardExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Quiz;
use App\Models\Committee;

class QuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with('committee')->paginate(15);
        return view('admin.quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $committees = Committee::active()->get();
        return view('admin.quizzes.create', compact('committees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'visibility' => 'required|in:global,private',
            'committee_id' => 'required_if:visibility,private|nullable|exists:committees,id',
        ]);

        // make quiz deactive by default
        $request->merge(['is_active' => 0]);
        
        Quiz::create($request->all());

        // return to view of this quiz to add questions
        return redirect()->route('admin.quizzes.show', Quiz::latest()->first())->with('success', 'Quiz created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        return view('admin.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        $committees = Committee::active()->get();
        return view('admin.quizzes.edit', compact('quiz', 'committees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'visibility' => 'required|in:global,private',
            'committee_id' => 'required_if:visibility,private|nullable|exists:committees,id',
        ]);

        $quiz->update($request->all());

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Toggle the active status of a quiz.
     */
    public function toggleActive(Quiz $quiz)
    {
        $quiz->is_active = !(bool) $quiz->is_active;
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
        return view('admin.quizzes.leaderboard', compact('quiz'));
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

    /**
     * Store questions generated from text via AI.
     */
    public function storeQuestionsFromText(Request $request, Quiz $quiz, \App\Services\AiOrderQuestions $aiService)
    {
        $request->validate([
            'questions_text' => 'required|string',
        ]);

        try {
            $count = $aiService->process($request->questions_text, $quiz->id);
            return response()->json(['success' => true, 'message' => "$count questions added successfully."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
