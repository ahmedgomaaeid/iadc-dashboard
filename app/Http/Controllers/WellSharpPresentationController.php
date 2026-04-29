<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Services\WellSharpQuizService;

class WellSharpPresentationController extends Controller
{
    public function show(Quiz $quiz)
    {
        if ($quiz->type !== 'wellsharp') {
            abort(404);
        }

        return view('wellsharp.presentation', ['quiz' => $quiz]);
    }

    public function state(Quiz $quiz)
    {
        if ($quiz->type !== 'wellsharp') {
            return response()->json(['status' => 'error', 'message' => 'Not found'], 404);
        }

        $state = WellSharpQuizService::getState($quiz->id);
        $leaderboard = WellSharpQuizService::getLeaderboard($quiz->id);

        if ($state['state'] === 'question' && $state['current_question'] > 0) {
            $questionData = WellSharpQuizService::getCurrentQuestionData($quiz->id);
            if ($questionData) {
                $state['question_data'] = $questionData;
            }
        }

        $state['total_questions'] = $quiz->questions()->count();

        return response()->json([
            'status' => 'success',
            'state' => $state,
            'leaderboard' => $leaderboard,
        ]);
    }
}
