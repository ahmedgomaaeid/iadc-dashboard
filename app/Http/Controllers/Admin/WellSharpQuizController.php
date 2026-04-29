<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Services\WellSharpQuizService;

class WellSharpQuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::where('type', 'wellsharp')->paginate(15);
        return view('admin.wellsharp_quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        return view('admin.wellsharp_quizzes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $quiz = Quiz::create([
            'name' => $request->name,
            'is_active' => 0,
            'type' => 'wellsharp',
            'visibility' => 'global',
        ]);

        return redirect()->route('admin.wellsharp_quizzes.show', $quiz)->with('success', 'WellSharp Quiz created successfully.');
    }

    public function show(Quiz $wellsharp_quiz)
    {
        return view('admin.wellsharp_quizzes.show', ['quiz' => $wellsharp_quiz]);
    }

    public function edit(Quiz $wellsharp_quiz)
    {
        return view('admin.wellsharp_quizzes.edit', ['quiz' => $wellsharp_quiz]);
    }

    public function update(Request $request, Quiz $wellsharp_quiz)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $wellsharp_quiz->update([
            'name' => $request->name,
        ]);

        return redirect()->route('admin.wellsharp_quizzes.index')->with('success', 'WellSharp Quiz updated successfully.');
    }

    public function destroy(Quiz $wellsharp_quiz)
    {
        WellSharpQuizService::clearSession($wellsharp_quiz->id);
        $wellsharp_quiz->delete();
        return redirect()->route('admin.wellsharp_quizzes.index')->with('success', 'WellSharp Quiz deleted successfully.');
    }

    public function toggleActive(Quiz $wellsharp_quiz)
    {
        $wellsharp_quiz->is_active = !(bool)$wellsharp_quiz->is_active;
        $wellsharp_quiz->save();

        if (!$wellsharp_quiz->is_active) {
            WellSharpQuizService::clearSession($wellsharp_quiz->id);
        }

        return back()->with('success', 'Quiz status updated.');
    }

    public function controlPanel(Quiz $wellsharp_quiz)
    {
        return view('admin.wellsharp_quizzes.control', ['quiz' => $wellsharp_quiz]);
    }

    public function state(Quiz $wellsharp_quiz)
    {
        $state = WellSharpQuizService::getState($wellsharp_quiz->id);
        $leaderboard = WellSharpQuizService::getLeaderboard($wellsharp_quiz->id);
        $totalQuestions = $wellsharp_quiz->questions()->count();

        if ($state['state'] === 'question' && $state['current_question'] > 0) {
            $questionData = WellSharpQuizService::getCurrentQuestionData($wellsharp_quiz->id);
            if ($questionData) {
                $state['question_data'] = $questionData;
            }
        }

        $state['total_questions'] = $totalQuestions;

        return response()->json([
            'status' => 'success',
            'state' => $state,
            'leaderboard' => $leaderboard,
        ]);
    }

    public function nextQuestion(Quiz $wellsharp_quiz)
    {
        $next = WellSharpQuizService::nextQuestion($wellsharp_quiz->id);
        if (!$next) {
            return response()->json(['status' => 'error', 'message' => 'Failed to load next question or no more questions'], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $next,
        ]);
    }

    public function addParticipant(Request $request, Quiz $wellsharp_quiz)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $participantId = WellSharpQuizService::addParticipant($wellsharp_quiz->id, $request->name);

        if (!$participantId) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add participant'], 500);
        }

        return response()->json([
            'status' => 'success',
            'participant_id' => $participantId,
        ]);
    }

    public function removeParticipant(Quiz $wellsharp_quiz, string $participantId)
    {
        WellSharpQuizService::removeParticipant($wellsharp_quiz->id, $participantId);
        return response()->json(['status' => 'success']);
    }

    public function addScore(Request $request, Quiz $wellsharp_quiz)
    {
        $request->validate([
            'participant_id' => 'required|string',
            'points' => 'required|integer|in:10,20,-5',
        ]);

        $success = WellSharpQuizService::addScore($wellsharp_quiz->id, $request->participant_id, $request->points);

        if (!$success) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add score'], 500);
        }

        return response()->json(['status' => 'success']);
    }

    public function skipQuestion(Quiz $wellsharp_quiz)
    {
        $success = WellSharpQuizService::skipQuestion($wellsharp_quiz->id);

        if (!$success) {
            return response()->json(['status' => 'error', 'message' => 'No question to skip'], 400);
        }

        return response()->json(['status' => 'success']);
    }

    public function clearSession(Quiz $wellsharp_quiz)
    {
        WellSharpQuizService::clearSession($wellsharp_quiz->id);
        return back()->with('success', 'Session cleared successfully.');
    }
}
