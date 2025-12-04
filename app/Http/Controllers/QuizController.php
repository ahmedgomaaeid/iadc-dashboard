<?php

namespace App\Http\Controllers;

use App\Services\QuizCacheService;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function showQuiz($id)
    {
        // Get quiz from database to check visibility
        $quiz = Quiz::with('committee')->find($id);
        
        if (!$quiz) {
            abort(404, 'Quiz not found');
        }
        
        // Check if quiz is active
        $quizCount = QuizCacheService::getQuizCount($id);
        if ($quizCount == 0) {
            abort(403, 'This quiz is not currently active');
        }
        
        // Check access for private quizzes
        if ($quiz->visibility === 'private') {
            // Check user guard for authentication
            $user = auth('user')->user();
            
            // If not authenticated, deny access
            if (!$user) {
                abort(403, 'This is a private quiz. Please log in to access it.');
            }
            
            // Check if user belongs to the quiz's committee
            $userCommitteeIds = $user->committees->pluck('id')->toArray();
            if (!in_array($quiz->committee_id, $userCommitteeIds)) {
                abort(403, 'You do not have access to this quiz. It is restricted to ' . $quiz->committee->name . ' members only.');
            }
        }
        
        // Get authenticated user info for auto-fill (check user guard)
        $userName = auth('user')->check() ? auth('user')->user()->name : '';
        $userEmail = auth('user')->check() ? auth('user')->user()->email : '';
        
        return view('quiz.show', compact('quizCount', 'id', 'quiz', 'userName', 'userEmail'));
    }

    public function addParticipant(Request $request, $quizId)
    {
        // Get quiz to check access
        $quiz = Quiz::find($quizId);
        
        if (!$quiz) {
            return response()->json(['error' => 'Quiz not found'], 404);
        }
        
        // Check access for private quizzes
        if ($quiz->visibility === 'private') {
            // Check user guard for authentication
            $user = auth('user')->user();
            
            if (!$user) {
                return response()->json(['error' => 'You must be logged in to join this private quiz'], 403);
            }
            
            $userCommitteeIds = $user->committees->pluck('id')->toArray();
            if (!in_array($quiz->committee_id, $userCommitteeIds)) {
                return response()->json(['error' => 'You do not have access to this quiz'], 403);
            }
        }
        
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
