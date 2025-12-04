<?php

namespace App\Http\Controllers;

use App\Services\QuizCacheService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function showQuiz($id)
    {
        // check the quiz existence and activeness in redis
        $quizCount = QuizCacheService::getQuizCount($id);
        return view('quiz.show', compact('quizCount', 'id'));
    }

    public function addParticipant(Request $request, $quizId)
    {
        if (empty($request->name) || empty($request->email)) {
            return response()->json(['error' => 'Name and email are required'], 400);
        }
        // Add participant to the quiz in Redis
        $participantId = QuizCacheService::addParticipant($quizId, $request->name, $request->email);
        return response()->json(['participant_id' => $participantId]);
    }
    public function getQuestion(Request $request, $quizId, $number)
    {
        // check the quiz is active
        if(QuizCacheService::getQuizCount($quizId) == 0) {
            return response()->json(['error' => 'Quiz is not active'], 403);
        }
        
        // Get participant ID from request
        $participantId = $request->query('participant_id');
        
        // Fetch question from Redis with participant's randomized order
        $question = QuizCacheService::getQuestion($quizId, $number, $participantId);
        if ($question) {
            unset($question['correct']); // don't expose the correct answer to the client
            return response()->json(['question' => $question]);
        } else {
            return response()->json(['error' => 'Question not found'], 404);
        }
    }

    public function answerQuestion(Request $request, $quizId)
    {
        $participantId = $request->participant_id;
        $questionNumber = $request->question_number;
        $answer = $request->answer;

        if (empty($participantId) || empty($questionNumber) || empty($answer)) {
            return response()->json(['error' => 'Participant ID, question number, and answer are required'], 400);
        }

        // check the
        // For simplicity, we'll just return a success response.

        return response()->json(['message' => 'Answer recorded successfully']);
    }
}
