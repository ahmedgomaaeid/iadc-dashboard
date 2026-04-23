<?php

namespace App\Http\Controllers\Highboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Committee;
use App\Services\InteractiveQuizService;
use App\Exports\InteractiveLeaderboardExport;
use Maatwebsite\Excel\Facades\Excel;

class InteractiveQuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::where('type', 'interactive')->with('committee')->paginate(15);
        return view('highboard.interactive_quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $highboard = auth('highboard')->user();
        $committees = Committee::active()->where('field_id', $highboard->field_id)->get();
        return view('highboard.interactive_quizzes.create', compact('committees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'visibility' => 'required|in:global,private',
            'committee_id' => 'required_if:visibility,private|nullable|exists:committees,id',
        ]);

        $request->merge([
            'is_active' => 0,
            'type' => 'interactive'
        ]);
        $quiz = Quiz::create($request->all());

        return redirect()->route('highboard.interactive_quizzes.show', $quiz)->with('success', 'Interactive Quiz created successfully.');
    }

    public function show(Quiz $interactive_quiz)
    {
        return view('highboard.interactive_quizzes.show', ['quiz' => $interactive_quiz]);
    }

    public function edit(Quiz $interactive_quiz)
    {
        $highboard = auth('highboard')->user();
        $committees = Committee::active()->where('field_id', $highboard->field_id)->get();
        return view('highboard.interactive_quizzes.edit', ['quiz' => $interactive_quiz, 'committees' => $committees]);
    }

    public function update(Request $request, Quiz $interactive_quiz)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
            'visibility' => 'required|in:global,private',
            'committee_id' => 'required_if:visibility,private|nullable|exists:committees,id',
        ]);

        $interactive_quiz->update($request->all());

        return redirect()->route('highboard.interactive_quizzes.index')->with('success', 'Interactive Quiz updated successfully.');
    }

    public function destroy(Quiz $interactive_quiz)
    {
        $interactive_quiz->delete();
        InteractiveQuizService::clearSession($interactive_quiz->id);
        return redirect()->route('highboard.interactive_quizzes.index')->with('success', 'Interactive Quiz deleted successfully.');
    }

    public function toggleActive(Quiz $interactive_quiz)
    {
        $interactive_quiz->is_active = !(bool)$interactive_quiz->is_active;
        $interactive_quiz->save();

        if (!$interactive_quiz->is_active) {
            // Only clear the quiz flow/timing keys — participant scores are preserved
            // so the leaderboard remains visible and exportable after deactivation.
            InteractiveQuizService::clearStateOnly($interactive_quiz->id);
        }

        return back()->with('success', 'Quiz status updated.');
    }

    public function leaderboard(Quiz $interactive_quiz)
    {
        return view('highboard.interactive_quizzes.leaderboard', ['quiz' => $interactive_quiz]);
    }

    public function exportLeaderboard(Quiz $interactive_quiz)
    {
        $filename = 'interactive_leaderboard_' . str_replace(' ', '_', $interactive_quiz->name) . '_' . date('Y-m-d_H-i-s') . '.xlsx';
        return Excel::download(new InteractiveLeaderboardExport($interactive_quiz->id, $interactive_quiz->name), $filename);
    }

    public function clearLeaderboard(Quiz $interactive_quiz)
    {
        InteractiveQuizService::clearSession($interactive_quiz->id);
        return back()->with('success', "Leaderboard cleared successfully.");
    }
    
    // Web API for the leaderboard
    public function state(Quiz $interactive_quiz)
    {
        $state = InteractiveQuizService::getState($interactive_quiz->id);
        $leaderboard = InteractiveQuizService::getLeaderboard($interactive_quiz->id);
        
        if ($state['state'] === 'question' && $state['current_question'] > 0) {
            $questions = $interactive_quiz->questions->sortBy('id')->values();
            if (isset($questions[$state['current_question'] - 1])) {
                $q = $questions[$state['current_question'] - 1];
                $state['question_data'] = [
                    'id' => $q->id,
                    'question' => $q->question,
                    'options' => [
                        'a' => $q->option_a,
                        'b' => $q->option_b,
                        'c' => $q->option_c,
                        'd' => $q->option_d,
                    ],
                ];
            }
        }
        
        return response()->json([
            'status' => 'success',
            'state' => $state,
            'leaderboard' => $leaderboard
        ]);
    }
    
    public function nextQuestion(Quiz $interactive_quiz)
    {
        $next = InteractiveQuizService::nextQuestion($interactive_quiz->id);
        if (!$next) {
            return response()->json(['status' => 'error', 'message' => 'Failed to load next question or no more questions'], 400);
        }
        
        return response()->json([
            'status' => 'success',
            'data' => $next
        ]);
    }
}
