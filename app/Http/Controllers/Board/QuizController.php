<?php

namespace App\Http\Controllers\Board;

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
        $board = auth('board')->user();
        $quizzes = Quiz::where('committee_id', $board->committee_id)
            ->with('committee')
            ->paginate(15);
        return view('board.quizzes.index', compact('quizzes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $board = auth('board')->user();
        return view('board.quizzes.create', compact('board'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $board = auth('board')->user();
        
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Force private visibility and board's committee
        $quizData = $request->all();
        $quizData['visibility'] = 'private';
        $quizData['is_active'] = 0;
        $quizData['committee_id'] = $board->committee_id;

        Quiz::create($quizData);

        // return to view of this quiz to add questions
        return redirect()->route('board.quizzes.show', Quiz::latest()->first())->with('success', 'Quiz created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only view quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        return view('board.quizzes.show', compact('quiz'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only edit quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        return view('board.quizzes.edit', compact('quiz', 'board'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only update quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Force private visibility and board's committee (don't allow changes)
        $quizData = $request->all();
        $quizData['visibility'] = 'private';
        $quizData['committee_id'] = $board->committee_id;

        $quiz->update($quizData);

        return redirect()->route('board.quizzes.index')->with('success', 'Quiz updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only delete quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        $quiz->delete();

        return redirect()->route('board.quizzes.index')->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Toggle the active status of a quiz.
     */
    public function toggleActive(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only toggle quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
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
        $board = auth('board')->user();
        
        // Ensure board can only view leaderboard for quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        return view('board.quizzes.leaderboard', compact('quiz'));
    }

    /**
     * Export leaderboard to Excel
     */
    public function exportLeaderboard(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only export leaderboard for quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
        $filename = 'leaderboard_' . str_replace(' ', '_', $quiz->name) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new LeaderboardExport($quiz->id, $quiz->name), $filename);
    }

    /**
     * Clear leaderboard data from cache
     */
    public function clearLeaderboard(Quiz $quiz)
    {
        $board = auth('board')->user();
        
        // Ensure board can only clear leaderboard for quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            abort(403, 'Unauthorized access to this quiz.');
        }
        
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
        $board = auth('board')->user();
        
        // Ensure board can only add questions to quizzes from their committee
        if ($quiz->committee_id !== $board->committee_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access to this quiz.'], 403);
        }

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
