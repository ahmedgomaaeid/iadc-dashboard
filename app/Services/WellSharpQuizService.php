<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class WellSharpQuizService
{
    private const TTL = 86400; // 24 hours

    /**
     * Get the current state of a WellSharp quiz.
     */
    public static function getState(int $quizId): array
    {
        try {
            $state = Redis::get("ws_quiz:{$quizId}:state") ?: 'lobby';
            $currentQuestion = (int) (Redis::get("ws_quiz:{$quizId}:current_question") ?: 0);

            return [
                'state' => $state,
                'current_question' => $currentQuestion,
            ];
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService getState error: " . $e->getMessage());
            return ['state' => 'lobby', 'current_question' => 0];
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

            $currentQuestion = (int) (Redis::get("ws_quiz:{$quizId}:current_question") ?: 0);
            $nextIndex = $currentQuestion; // 0-based index

            if (!isset($questions[$nextIndex])) {
                Redis::set("ws_quiz:{$quizId}:state", 'finished');
                return ['state' => 'finished'];
            }

            $question = $questions[$nextIndex];

            Redis::set("ws_quiz:{$quizId}:state", 'question');
            Redis::set("ws_quiz:{$quizId}:current_question", $nextIndex + 1);

            return [
                'state' => 'question',
                'current_question' => $nextIndex + 1,
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
            Log::error("WellSharpQuizService nextQuestion error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Add a participant to the WellSharp quiz.
     */
    public static function addParticipant(int $quizId, string $name): ?string
    {
        try {
            $participantId = uniqid('ws_', true);
            $participantKey = "ws_quiz:{$quizId}:participant:{$participantId}";

            $payload = json_encode([
                'name' => $name,
            ]);

            Redis::setex($participantKey, self::TTL, $payload);

            // Initialize score to 0
            $scoreKey = "ws_quiz:{$quizId}:score:{$participantId}";
            Redis::setex($scoreKey, self::TTL, 0);

            return $participantId;
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService addParticipant error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Remove a participant from the WellSharp quiz.
     */
    public static function removeParticipant(int $quizId, string $participantId): bool
    {
        try {
            Redis::del("ws_quiz:{$quizId}:participant:{$participantId}");
            Redis::del("ws_quiz:{$quizId}:score:{$participantId}");
            return true;
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService removeParticipant error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Award points to a participant and switch state to leaderboard.
     */
    public static function addScore(int $quizId, string $participantId, int $points): bool
    {
        try {
            $scoreKey = "ws_quiz:{$quizId}:score:{$participantId}";

            if (!Redis::exists($scoreKey) && !Redis::exists("ws_quiz:{$quizId}:participant:{$participantId}")) {
                return false;
            }

            Redis::incrby($scoreKey, $points);

            // Switch state to leaderboard
            Redis::set("ws_quiz:{$quizId}:state", 'leaderboard');

            return true;
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService addScore error: " . $e->getMessage());
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
            $scorePattern = "ws_quiz:{$quizId}:score:*";
            $scoreKeys = Redis::keys($scorePattern);

            foreach ($scoreKeys as $scoreKey) {
                preg_match('/score:([^:]+)$/', $scoreKey, $matches);
                if (!isset($matches[1])) continue;

                $participantId = $matches[1];
                $participantInfoKey = "ws_quiz:{$quizId}:participant:{$participantId}";

                $participantData = Redis::get($participantInfoKey);
                if (!$participantData) continue;

                $participant = json_decode($participantData, true);

                $score = (int) Redis::get("ws_quiz:{$quizId}:score:{$participantId}");

                $leaderboard[] = [
                    'participant_id' => $participantId,
                    'name' => $participant['name'] ?? 'Unknown',
                    'score' => $score,
                ];
            }

            usort($leaderboard, function ($a, $b) {
                return $b['score'] - $a['score'];
            });

            foreach ($leaderboard as $index => &$entry) {
                $entry['rank'] = $index + 1;
            }

            return $leaderboard;
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService getLeaderboard error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get the current question data.
     */
    public static function getCurrentQuestionData(int $quizId): ?array
    {
        try {
            $currentQuestion = (int) (Redis::get("ws_quiz:{$quizId}:current_question") ?: 0);
            if ($currentQuestion <= 0) return null;

            $quiz = Quiz::with('questions')->find($quizId);
            if (!$quiz) return null;

            $questions = $quiz->questions->sortBy('id')->values();
            $index = $currentQuestion - 1;

            if (!isset($questions[$index])) return null;

            $q = $questions[$index];
            return [
                'id' => $q->id,
                'question' => $q->question,
                'options' => [
                    'a' => $q->option_a,
                    'b' => $q->option_b,
                    'c' => $q->option_c,
                    'd' => $q->option_d,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService getCurrentQuestionData error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Clear an entire WellSharp quiz session.
     */
    public static function clearSession(int $quizId): void
    {
        try {
            $keys = Redis::keys("ws_quiz:{$quizId}:*");
            foreach ($keys as $key) {
                $prefix = config('database.redis.options.prefix', '');
                if ($prefix && strpos($key, $prefix) === 0) {
                    $key = substr($key, strlen($prefix));
                }
                Redis::del($key);
            }
        } catch (\Throwable $e) {
            Log::error("WellSharpQuizService clearSession error: " . $e->getMessage());
        }
    }
}
