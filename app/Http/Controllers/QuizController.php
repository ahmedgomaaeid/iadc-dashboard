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
        
        // Time taken logic could be added here if sent from frontend

        if (empty($participantId) || empty($questionNumber) || empty($answer)) {
            return response()->json(['error' => 'Participant ID, question number, and answer are required'], 400);
        }

        // Get the question to check the correct answer
        // Note: getQuestion in Controller usually removes 'correct'. We need to be careful.
        // But QuizCacheService methods are public static.
        
        // We need the ACTUAL question data including 'correct' field.
        // QuizCacheService::getQuestion returns the question data.
        // But in getQuestion method of Controller, it unsets 'correct'.
        // Here we are calling the Service directly/simulating.
        // Wait, QuizCacheService::getQuestion logic:
        // "return json_decode($data, true);" - this INCLUDES 'correct' if it was stored.
        // store() method stores 'correct'.
        // So yes, we get 'correct' here.

        $question = QuizCacheService::getQuestion($quizId, $questionNumber, $participantId);
        
        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        $isCorrect = ($answer == $question['correct']);
        
        // Record answer
        QuizCacheService::recordAnswer($quizId, $participantId, $questionNumber, $answer, $isCorrect, 0); // timeTaken 0 for now
        
        // Update score if correct
        // Check if already answered correctly? 
        // Redis 'answer' key overwrites.
        // Logic for score:
        // We should only add score if it wasn't already answered correctly?
        // Or assumes one attempt per question?
        // Implementation: Just add score if correct? 
        // But if they resubmit, we might double count?
        // Ideally we check previous answer.
        // For now, assuming single attempt per question flow from frontend.
        // Or we can check if variable exists, but that's complex.
        // Let's assume add score if correct, but we might want to prevent re-answering.
        // Since this is a simple implementation: 
        if ($isCorrect) {
             // We need to check if we already gave points for this question to this participant?
             // Since we don't track "points given for question X", we rely on the flow.
             // But safer to just recount at the end? 
             // Requirement says: "record like he get and add max score".
             // Maybe calculating score at 'finishQuiz' is better/safer against replays.
             // But realtime feedback might be needed.
             // I'll update the running score anyway.
             QuizCacheService::addToScore($quizId, $participantId, 1);
        }

        return response()->json(['message' => 'Answer recorded successfully', 'is_correct' => $isCorrect]);
    }

    public function finishQuiz(Request $request, $quizId)
    {
        $participantId = $request->participant_id;
        
        if (empty($participantId)) {
            return response()->json(['error' => 'Participant ID is required'], 400);
        }

        // Calculate final score
        // Option 1: Trust running score in Redis
        // $score = QuizCacheService::getParticipantScore($quizId, $participantId);
        
        // Option 2: Recalculate from answers (Safest)
        $score = 0;
        $totalQuestions = QuizCacheService::getQuizCount($quizId);
        
        for ($i = 1; $i <= $totalQuestions; $i++) {
            // Get answer
            $key = "quiz:{$quizId}:participant:{$participantId}:q:{$i}:answer";
            $payload = \Illuminate\Support\Facades\Redis::get($key);
            if ($payload) {
                $data = json_decode($payload, true);
                if (isset($data['is_correct']) && $data['is_correct']) {
                    $score++;
                }
            }
        }
        
        // Record Evaluation if User is logged in
        if (auth('user')->check()) {
            $user = auth('user')->user();
            
            // Verify participant belongs to this user (email match)
            $participantKey = "quiz:{$quizId}:participant:{$participantId}";
            $pData = \Illuminate\Support\Facades\Redis::get($participantKey);
            $pInfo = json_decode($pData, true);
            
            if ($pInfo && $pInfo['email'] === $user->email) {
                // Record/Update Evaluation
                 \App\Models\UserEvaluation::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'related_type' => \App\Models\Quiz::class,
                        'related_id' => $quizId,
                        'type' => 'quiz',
                    ],
                    [
                        'score' => $score,
                        'max_score' => $totalQuestions,
                        'committee_id' => \App\Models\Quiz::find($quizId)->committee_id ?? null,
                    ]
                );
            }
        }

        return response()->json([
            'message' => 'Quiz finished',
            'score' => $score,
            'max_score' => $totalQuestions
        ]);
    }
}
