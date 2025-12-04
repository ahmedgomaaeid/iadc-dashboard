<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\QuizCacheService;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function addParticipant(Request $request, $quizId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        // Add participant to the quiz in Redis
        $participantId = QuizCacheService::addParticipant(
            $quizId,
            $validated['name'],
            $validated['email']
        );

        if (!$participantId) {
            return response()->json(['error' => 'Failed to add participant'], 500);
        }

        return response()->json(['participant_id' => $participantId]);
    }

    public function getQuestion(Request $request, $quizId, $number)
    {
        // Check if the quiz is active
        if (QuizCacheService::getQuizCount($quizId) == 0) {
            return response()->json(['error' => 'Quiz is not active'], 403);
        }

        // Get participant ID from request
        $participantId = $request->input('participant_id');

        if (empty($participantId)) {
            return response()->json(['error' => 'Participant ID is required'], 400);
        }

        // Fetch question from Redis
        $question = QuizCacheService::getQuestion($quizId, $number);

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Store the start time for this question for this participant
        QuizCacheService::setQuestionStartTime($quizId, $participantId, $number);

        // Get the time limit from the question, default to 30 if not set
        $timeLimit = $question['time_limit'] ?? 30;

        // Don't expose the correct answer to the client
        unset($question['correct']);

        return response()->json([
            'question' => $question,
            'time_limit' => $timeLimit,
            'started_at' => time()
        ]);
    }

    public function answerQuestion(Request $request, $quizId)
    {
        $participantId = $request->participant_id;
        $questionNumber = $request->question_number;
        $answer = strtoupper($request->answer);

        // Validate required fields
        if (empty($participantId) || empty($questionNumber) || empty($answer)) {
            return response()->json([
                'error' => 'Participant ID, question number, and answer are required'
            ], 400);
        }

        // Validate answer format
        if (!in_array($answer, ['A', 'B', 'C', 'D'])) {
            return response()->json([
                'error' => 'Answer must be A, B, C, or D'
            ], 400);
        }

        // Check if quiz is active
        if (QuizCacheService::getQuizCount($quizId) == 0) {
            return response()->json(['error' => 'Quiz is not active'], 403);
        }

        // Get the question to verify correct answer
        $question = QuizCacheService::getQuestion($quizId, $questionNumber);

        if (!$question) {
            return response()->json(['error' => 'Question not found'], 404);
        }

        // Get start time for this question
        $startTime = QuizCacheService::getQuestionStartTime($quizId, $participantId, $questionNumber);

        if (!$startTime) {
            return response()->json([
                'error' => 'Question was not started. Please fetch the question first.'
            ], 400);
        }

        // Calculate time taken
        $currentTime = time();
        $timeTaken = $currentTime - $startTime;

        // Get the time limit from the question, default to 30 if not set
        $timeLimit = $question['time_limit'] ?? 30;

        // Check if answer is within time limit
        $isOnTime = $timeTaken <= $timeLimit;

        if (!$isOnTime) {
            // Still record the answer even if time exceeded
            QuizCacheService::recordAnswer(
                $quizId,
                $participantId,
                $questionNumber,
                $answer,
                false, // Not correct if time exceeded
                $timeTaken
            );

            // Check if this was the last question
            $totalQuestions = QuizCacheService::getQuizCount($quizId);
            $isLastQuestion = $questionNumber >= $totalQuestions;

            if ($isLastQuestion) {
                return response()->json([
                    'error' => 'Time limit exceeded',
                    'message' => 'Quiz completed! Thank you for participating.',
                    'completed' => true,
                    'total_questions' => $totalQuestions
                ], 400);
            }

            return response()->json([
                'error' => 'Time limit exceeded',
                'message' => 'Answer recorded. Moving to next question.',
                'next_question' => $questionNumber + 1,
                'total_questions' => $totalQuestions
            ], 400);
        }

        // Check if answer is correct
        $correctAnswer = strtoupper($question['correct']);
        $isCorrect = ($answer === $correctAnswer);

        // Award points: 1 for correct answer on time, 0 otherwise
        $pointsAwarded = ($isCorrect && $isOnTime) ? 1 : 0;

        if ($pointsAwarded > 0) {
            QuizCacheService::addToScore($quizId, $participantId, $pointsAwarded);
        }

        // Record the answer
        QuizCacheService::recordAnswer(
            $quizId,
            $participantId,
            $questionNumber,
            $answer,
            $isCorrect,
            $timeTaken
        );

        // Check if this was the last question
        $totalQuestions = QuizCacheService::getQuizCount($quizId);
        $isLastQuestion = $questionNumber >= $totalQuestions;

        if ($isLastQuestion) {
            return response()->json([
                'message' => 'Quiz completed! Thank you for participating.',
                'completed' => true,
                'total_questions' => $totalQuestions
            ]);
        }

        return response()->json([
            'message' => 'Answer recorded successfully',
            'next_question' => $questionNumber + 1,
            'total_questions' => $totalQuestions
        ]);
    }

    /**
     * Get leaderboard data for a quiz
     */
    public function getLeaderboard($quizId)
    {
        $leaderboard = QuizCacheService::getLeaderboard($quizId);

        return response()->json([
            'success' => true,
            'leaderboard' => $leaderboard,
            'total_participants' => count($leaderboard),
            'timestamp' => time()
        ]);
    }
}
