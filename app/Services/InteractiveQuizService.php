<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class InteractiveQuizService
{
    /**
     * Get the current state of an interactive quiz.
     */
    public static function getState(int $quizId): array
    {
        try {
            $stateKey = "i_quiz:{$quizId}:state";
            $state = Redis::get($stateKey) ?: 'lobby';

            $currentQuestion = (int) Redis::get("i_quiz:{$quizId}:current_question") ?: 0;
            $startTime = (int) Redis::get("i_quiz:{$quizId}:start_time") ?: 0;
            $timeLimit = (int) Redis::get("i_quiz:{$quizId}:time_limit") ?: 0;

            // Compute if question time is up and state should be automatically switched to leaderboard
            if ($state === 'question' && time() > ($startTime + $timeLimit)) {
                $state = 'leaderboard';
                Redis::set($stateKey, 'leaderboard');
            }

            return [
                'state' => $state,
                'current_question' => $currentQuestion,
                'start_time' => $startTime,
                'time_limit' => $timeLimit,
                'server_time' => time(),
            ];
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService getState error: " . $e->getMessage());
            return ['state' => 'lobby', 'current_question' => 0, 'start_time' => 0, 'time_limit' => 0];
        }
    }

    /**
     * Admin triggers the next question.
     */
    public static function nextQuestion(int $quizId): ?array
    {
        try {
            $quiz = Quiz::with('questions')->find($quizId);
            if (!$quiz) return null;

            $questions = $quiz->questions->sortBy('id')->values();
            
            $currentQuestion = (int) Redis::get("i_quiz:{$quizId}:current_question") ?: 0;
            $nextIndex = $currentQuestion; // 0-based index means index 0 is question 1
            
            if (!isset($questions[$nextIndex])) {
                Redis::set("i_quiz:{$quizId}:state", 'finished');
                return ['state' => 'finished'];
            }

            $question = $questions[$nextIndex];
            $timeLimit = $question->time_limit ?? 30;

            Redis::set("i_quiz:{$quizId}:state", 'question');
            Redis::set("i_quiz:{$quizId}:current_question", $nextIndex + 1);
            Redis::set("i_quiz:{$quizId}:start_time", time());
            Redis::set("i_quiz:{$quizId}:time_limit", $timeLimit);

            return [
                'state' => 'question',
                'current_question' => $nextIndex + 1,
                'start_time' => time(),
                'time_limit' => $timeLimit,
                'question_data' => [
                    'id' => $question->id,
                    'question' => $question->question,
                    'options' => [
                        'a' => $question->option_a,
                        'b' => $question->option_b,
                        'c' => $question->option_c,
                        'd' => $question->option_d,
                    ],
                ]
            ];
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService nextQuestion error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a participant to the interactive quiz.
     */
    public static function addParticipant(int $quizId, string $name, string $email): ?string
    {
        try {
            $emailKey = "i_quiz:{$quizId}:email:" . md5(strtolower($email));
            
            if (Redis::exists($emailKey)) {
                return Redis::get($emailKey); // Return existing participant ID
            }

            $participantId = uniqid('ip_', true);
            $participantKey = "i_quiz:{$quizId}:participant:{$participantId}";
            
            $payload = json_encode([
                'name' => $name,
                'email' => $email,
            ]);
            
            Redis::setex($participantKey, 86400, $payload);
            Redis::setex($emailKey, 86400, $participantId);
            
            // Initialize score to 0
            $scoreKey = "i_quiz:{$quizId}:score:{$participantId}";
            Redis::setex($scoreKey, 86400, 0);

            return $participantId;
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService addParticipant error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Record an answer and calculate points on the fly.
     */
    public static function recordAnswer(int $quizId, string $participantId, int $questionIndex, string $answer): bool
    {
        try {
            $stateData = self::getState($quizId);
            
            // Cannot answer if not in question state or answering a different question
            if ($stateData['state'] !== 'question' || $stateData['current_question'] !== $questionIndex) {
                return false;
            }

            // Check if already answered this question
            $answeredKey = "i_quiz:{$quizId}:participant:{$participantId}:q:{$questionIndex}:answered";
            if (Redis::exists($answeredKey)) {
                return false; 
            }

            $quiz = Quiz::with('questions')->find($quizId);
            $questions = $quiz->questions->sortBy('id')->values();
            
            if (!isset($questions[$questionIndex - 1])) {
                return false;
            }

            $question = $questions[$questionIndex - 1];
            $timeTaken = time() - $stateData['start_time'];
            $timeLimit = $stateData['time_limit'];
            
            $isCorrect = (strtolower($answer) === strtolower($question->correct_option));
            $points = 0;

            if ($isCorrect) {
                $timeRemaining = max(0, $timeLimit - $timeTaken);
                $points = 1 * $timeRemaining; // Award points based on time
            }

            // Record answer and points
            Redis::setex($answeredKey, 86400, 1);
            if ($points > 0) {
                $scoreKey = "i_quiz:{$quizId}:score:{$participantId}";
                Redis::incrby($scoreKey, $points);
            }

            return true;
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService recordAnswer error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the live leaderboard.
     */
    public static function getLeaderboard(int $quizId): array
    {
        try {
            $leaderboard = [];
            $scorePattern = "i_quiz:{$quizId}:score:*";
            $scoreKeys = Redis::keys($scorePattern);

            foreach ($scoreKeys as $scoreKey) {
                preg_match('/score:([^:]+)$/', $scoreKey, $matches);
                if (!isset($matches[1])) continue;

                $participantId = $matches[1];
                $participantInfoKey = "i_quiz:{$quizId}:participant:{$participantId}";
                
                $participantData = Redis::get($participantInfoKey);
                if (!$participantData) continue;
                
                $participant = json_decode($participantData, true);
                
                // Get Redis key value - Note Redis::keys returns fully qualified keys, Redis::get uses logical keys
                // We use our clean key to get the score.
                $score = (int) Redis::get("i_quiz:{$quizId}:score:{$participantId}");

                $leaderboard[] = [
                    'participant_id' => $participantId,
                    'name' => $participant['name'] ?? 'Unknown',
                    'email' => $participant['email'] ?? '',
                    'score' => $score,
                ];
            }

            usort($leaderboard, function($a, $b) {
                return $b['score'] - $a['score'];
            });

            foreach ($leaderboard as $index => &$entry) {
                $entry['rank'] = $index + 1;
            }

            return $leaderboard;
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService getLeaderboard error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Helper to clear an interactive quiz session
     */
    public static function clearSession(int $quizId): void
    {
        try {
            Redis::del("i_quiz:{$quizId}:state");
            Redis::del("i_quiz:{$quizId}:current_question");
            Redis::del("i_quiz:{$quizId}:start_time");
            Redis::del("i_quiz:{$quizId}:time_limit");
            
            $keys = Redis::keys("i_quiz:{$quizId}:*");
            foreach($keys as $key) {
                // Determine prefix properly since Redis::keys might include database prefix defined in config
                $prefix = config('database.redis.options.prefix', '');
                if ($prefix && strpos($key, $prefix) === 0) {
                    $key = substr($key, strlen($prefix));
                }
                Redis::del($key);
            }
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService clearSession error: " . $e->getMessage());
        }
    }

    /**
     * Clear only the quiz flow/state keys when deactivating a quiz.
     * Participant scores and data are preserved so the leaderboard
     * remains visible and exportable until Redis TTLs naturally expire.
     */
    public static function clearStateOnly(int $quizId): void
    {
        try {
            Redis::del("i_quiz:{$quizId}:state");
            Redis::del("i_quiz:{$quizId}:current_question");
            Redis::del("i_quiz:{$quizId}:start_time");
            Redis::del("i_quiz:{$quizId}:time_limit");
        } catch (\Throwable $e) {
            Log::error("InteractiveQuizService clearStateOnly error: " . $e->getMessage());
        }
    }
}
