<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Services\InteractiveQuizService;

class InteractiveQuizController extends Controller
{
    public function show(Quiz $quiz)
    {
        if ($quiz->type !== 'interactive') {
            abort(404);
        }

        if (!$quiz->is_active) {
            return redirect('/')->with('error', 'This quiz is not currently available.');
        }

        // If user is already in session for this quiz, let them in
        $participantId = session("interactive_quiz_{$quiz->id}_participant");
        if ($participantId) {
            return view('interactive_quiz.show', compact('quiz', 'participantId'));
        }

        return view('interactive_quiz.join', compact('quiz'));
    }

    public function join(Request $request, Quiz $quiz)
    {
        if ($quiz->type !== 'interactive' || !$quiz->is_active) {
            return redirect()->back()->with('error', 'Quiz unavailable.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $participantId = InteractiveQuizService::addParticipant($quiz->id, $request->name, $request->email);

        if ($participantId) {
            session(["interactive_quiz_{$quiz->id}_participant" => $participantId]);
            return redirect()->route('interactive_quiz.show', $quiz->id);
        }

        return redirect()->back()->with('error', 'Failed to join the quiz or email already used.');
    }

    public function state(Quiz $quiz)
    {
        $participantId = session("interactive_quiz_{$quiz->id}_participant");
        if (!$participantId) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $state = InteractiveQuizService::getState($quiz->id);
        
        // If state is leaderboard, we might want to return the actual leaderboard data
        if ($state['state'] === 'leaderboard') {
            $state['leaderboard_data'] = InteractiveQuizService::getLeaderboard($quiz->id);
        }
        
        // Let's also pass the participant's score
        $state['my_score'] = (int) \Illuminate\Support\Facades\Redis::get("i_quiz:{$quiz->id}:score:{$participantId}");
        
        // If question state, we ONLY return question_data if we are currently in `question` 
        // to prevent users from scraping upcoming questions
        if ($state['state'] === 'question') {
            // We need to fetch the question text securely
            $questions = $quiz->questions->sortBy('id')->values();
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
                
                // check if user already answered
                $answeredKey = "i_quiz:{$quiz->id}:participant:{$participantId}:q:{$state['current_question']}:answered";
                $state['has_answered'] = \Illuminate\Support\Facades\Redis::exists($answeredKey) ? true : false;
            }
        }

        return response()->json(['status' => 'success', 'data' => $state]);
    }

    public function answer(Request $request, Quiz $quiz)
    {
        $participantId = session("interactive_quiz_{$quiz->id}_participant");
        if (!$participantId) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $request->validate([
            'question_index' => 'required|integer',
            'answer' => 'required|string|in:a,b,c,d'
        ]);

        $recorded = InteractiveQuizService::recordAnswer(
            $quiz->id, 
            $participantId, 
            $request->question_index, 
            $request->answer
        );

        if ($recorded) {
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Failed to record answer (time up, already answered, or wrong state)'], 400);
    }
}
