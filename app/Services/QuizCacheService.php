<?php

namespace App\Services;

use App\Models\Quiz;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class QuizCacheService
{
    protected static function questionKey(Quiz $quiz, int $number): string
    {
        return "quiz:{$quiz->id}:q:{$number}";
    }

    protected static function countKey(Quiz $quiz): string
    {
        return "quiz:{$quiz->id}:q_count";
    }

    protected static function activeKey(Quiz $quiz): string
    {
        // Fast active/inactive check key
        return "quiz-{$quiz->id}-check";
    }

    /**
     * Store quiz questions into Redis as JSON.
     */
    public static function store(Quiz $quiz): void
    {
        // Ensure questions relation is loaded
        if (!$quiz->relationLoaded('questions')) {
            $quiz->load('questions');
        }

        // Define a stable ordering for numbering (by id ascending)
        $questions = $quiz->questions->sortBy('id')->values();

        // Clear existing cached questions for this quiz
        self::clear($quiz);

        // Store each question individually: quiz:{id}:q:{number}
        $count = $questions->count();
        foreach ($questions as $index => $q) {
            $number = $index + 1; // 1-based numbering
            $payload = json_encode([
                'id' => $q->id,
                'question' => $q->question,
                'options' => [
                    'a' => $q->option_a,
                    'b' => $q->option_b,
                    'c' => $q->option_c,
                    'd' => $q->option_d,
                ],
                'correct' => $q->correct_option,
                'number' => $number,
                'time_limit' => $q->time_limit ?? 30,
            ]);

            try {
                Redis::set(self::questionKey($quiz, $number), $payload);
            } catch (\Throwable $e) {
                Log::warning('Failed to write quiz question to Redis', [
                    'quiz_id' => $quiz->id,
                    'number' => $number,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Save count for easy iteration/cleanup
        try {
            Redis::set(self::countKey($quiz), (string) $count);
        } catch (\Throwable $e) {
            Log::warning('Failed to write quiz count to Redis', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Also set active flag if quiz currently marked active in DB
        if ($quiz->is_active) {
            self::setActiveFlag($quiz);
        }
    }

    /**
     * Remove quiz questions from Redis.
     */
    public static function clear(Quiz $quiz): void
    {
        try {
            // Delete per-question keys using the stored count
            $count = (int) (Redis::get(self::countKey($quiz)) ?: 0);
            if ($count > 0) {
                for ($i = 1; $i <= $count; $i++) {
                    Redis::del(self::questionKey($quiz, $i));
                }
            }
            Redis::del(self::countKey($quiz));
            Redis::del(self::activeKey($quiz));
        } catch (\Throwable $e) {
            Log::warning('Failed to clear quiz questions from Redis', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Set the active flag key for quick status check.
     */
    public static function setActiveFlag(Quiz $quiz): void
    {
        try {
            Redis::set(self::activeKey($quiz), 'active');
        } catch (\Throwable $e) {
            Log::warning('Failed to set active flag in Redis', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Remove the active flag key.
     */
    public static function clearActiveFlag(Quiz $quiz): void
    {
        try {
            Redis::del(self::activeKey($quiz));
        } catch (\Throwable $e) {
            Log::warning('Failed to clear active flag in Redis', [
                'quiz_id' => $quiz->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public static function getQuizCount(int $quizId): int
    {
        try {
            $count = Redis::get("quiz:{$quizId}:q_count");
            return $count !== null ? (int) $count : 0;
        } catch (\Throwable $e) {
            Log::warning('Failed to get quiz count from Redis', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Check if an email has already participated in a quiz.
     */
    public static function hasEmailParticipated(int $quizId, string $email): bool
    {
        try {
            $emailKey = "quiz:{$quizId}:email:" . md5(strtolower($email));
            return Redis::exists($emailKey) > 0;
        } catch (\Throwable $e) {
            Log::warning('Failed to check email participation in Redis', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    public static function addParticipant(int $quizId, string $name, string $email): ?string
    {
        try {
            // Check if email has already participated
            if (self::hasEmailParticipated($quizId, $email)) {
                Log::info('Email already participated in quiz', [
                    'quiz_id' => $quizId,
                    'email' => $email,
                ]);
                return null; // Email already used
            }

            $participantId = uniqid('p_', true);
            $participantKey = "quiz:{$quizId}:participant:{$participantId}";
            $payload = json_encode([
                'name' => $name,
                'email' => $email,
            ]);
            Redis::setex($participantKey, 86400, $payload);
            
            // Store email participation record
            $emailKey = "quiz:{$quizId}:email:" . md5(strtolower($email));
            Redis::setex($emailKey, 86400, $participantId);
            
            // Also initialize the score to 0
            $scoreKey = "quiz:{$quizId}:participant:{$participantId}:score";
            Redis::setex($scoreKey, 86400, 0);
            
            // Generate randomized question order for this participant
            $questionCount = self::getQuizCount($quizId);
            if ($questionCount > 0) {
                // Create array of question numbers [1, 2, 3, ...]
                $questionOrder = range(1, $questionCount);
                // Shuffle the array
                shuffle($questionOrder);
                // Store the randomized order
                $orderKey = "quiz:{$quizId}:participant:{$participantId}:question_order";
                Redis::setex($orderKey, 86400, json_encode($questionOrder));
            }

            return $participantId;
        } catch (\Throwable $e) {
            Log::warning('Failed to add participant to quiz in Redis', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Get question based on participant's randomized order.
     * @param int $quizId Quiz ID
     * @param int $number The sequential question number (1, 2, 3, ...) from participant's perspective
     * @param string|null $participantId Participant ID to get their specific randomized order
     * @return array|null Question data
     */
    public static function getQuestion(int $quizId, int $number, ?string $participantId = null): ?array
    {
        try {
            // If participant ID is provided, use their randomized order
            if ($participantId) {
                $orderKey = "quiz:{$quizId}:participant:{$participantId}:question_order";
                $orderData = Redis::get($orderKey);
                
                if ($orderData) {
                    $questionOrder = json_decode($orderData, true);
                    // Get the actual question number from the randomized order
                    // $number is 1-indexed, so we need to subtract 1 for array access
                    if (isset($questionOrder[$number - 1])) {
                        $actualQuestionNumber = $questionOrder[$number - 1];
                        $questionKey = "quiz:{$quizId}:q:{$actualQuestionNumber}";
                        $data = Redis::get($questionKey);
                        if ($data) {
                            $question = json_decode($data, true);
                            // Override the number to show the sequential order to participant
                            $question['number'] = $number;
                            return $question;
                        }
                    }
                }
            }
            
            // Fallback to sequential order if no participant ID or order not found
            $questionKey = "quiz:{$quizId}:q:{$number}";
            $data = Redis::get($questionKey);
            if ($data) {
                return json_decode($data, true);
            } else {
                return null;
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to get quiz question from Redis', [
                'quiz_id' => $quizId,
                'number' => $number,
                'participant_id' => $participantId ?? 'none',
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Store the timestamp when a participant starts answering a question.
     */
    public static function setQuestionStartTime(int $quizId, string $participantId, int $questionNumber): void
    {
        try {
            $key = "quiz:{$quizId}:participant:{$participantId}:q:{$questionNumber}:start_time";
            Redis::setex($key, 3600, (string) time()); // Expire after 1 hour
        } catch (\Throwable $e) {
            Log::warning('Failed to set question start time in Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'question_number' => $questionNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get the timestamp when a participant started answering a question.
     */
    public static function getQuestionStartTime(int $quizId, string $participantId, int $questionNumber): ?int
    {
        try {
            $key = "quiz:{$quizId}:participant:{$participantId}:q:{$questionNumber}:start_time";
            $time = Redis::get($key);
            return $time !== null ? (int) $time : null;
        } catch (\Throwable $e) {
            Log::warning('Failed to get question start time from Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'question_number' => $questionNumber,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Record an answer for a participant.
     */
    public static function recordAnswer(int $quizId, string $participantId, int $questionNumber, string $answer, bool $isCorrect, int $timeTaken): void
    {
        try {
            $key = "quiz:{$quizId}:participant:{$participantId}:q:{$questionNumber}:answer";
            $payload = json_encode([
                'answer' => $answer,
                'is_correct' => $isCorrect,
                'time_taken' => $timeTaken,
                'answered_at' => time(),
            ]);
            Redis::setex($key, 86400, $payload); // Expire after 24 hours
        } catch (\Throwable $e) {
            Log::warning('Failed to record answer in Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'question_number' => $questionNumber,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get or initialize participant score.
     */
    public static function getParticipantScore(int $quizId, string $participantId): int
    {
        try {
            $key = "quiz:{$quizId}:participant:{$participantId}:score";
            $score = Redis::get($key);
            return $score !== null ? (int) $score : 0;
        } catch (\Throwable $e) {
            Log::warning('Failed to get participant score from Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }

    /**
     * Add points to participant score.
     */
    public static function addToScore(int $quizId, string $participantId, int $points): void
    {
        try {
            $key = "quiz:{$quizId}:participant:{$participantId}:score";
            Redis::incrby($key, $points);
            Redis::persist($key); // Remove expiration, make it persistent
        } catch (\Throwable $e) {
            Log::warning('Failed to update participant score in Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'points' => $points,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get leaderboard data for a quiz.
     * Returns array of participants with their names and scores, sorted by score descending.
     */
    public static function getLeaderboard(int $quizId): array
    {
        try {
            $leaderboard = [];

            // Find all participant score keys first, as they are a reliable indicator of a participant
            $scorePattern = "quiz:{$quizId}:participant:*:score";
            $scoreKeys = Redis::keys($scorePattern);

            foreach ($scoreKeys as $scoreKey) {
                // Extract participant ID from the score key
                // quiz:{quizId}:participant:{participantId}:score
                preg_match('/participant:([^:]+):score/', $scoreKey, $matches);
                if (!isset($matches[1])) {
                    continue;
                }

                $participantId = $matches[1];

                // Construct the participant info key (without prefix)
                $participantInfoKey = "quiz:{$quizId}:participant:{$participantId}";

                // Get participant info
                $participantData = Redis::get($participantInfoKey);
                if (!$participantData) {
                    // If info is missing, we can still show the score with an unknown name
                    $participant = ['name' => 'Unknown Participant', 'email' => ''];
                } else {
                    $participant = json_decode($participantData, true);
                }

                // Reconstruct the score key without the prefix
                $scoreKeyWithoutPrefix = "quiz:{$quizId}:participant:{$participantId}:score";
                $score = (int) Redis::get($scoreKeyWithoutPrefix);

                $leaderboard[] = [
                    'participant_id' => $participantId,
                    'name' => $participant['name'] ?? 'Unknown',
                    'email' => $participant['email'] ?? '',
                    'score' => $score,
                ];
            }

            // Sort by score descending
            usort($leaderboard, function($a, $b) {
                return $b['score'] - $a['score'];
            });

            // Add rank
            foreach ($leaderboard as $index => &$entry) {
                $entry['rank'] = $index + 1;
            }

            return $leaderboard;
        } catch (\Throwable $e) {
            Log::warning('Failed to get leaderboard from Redis', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Clear all leaderboard data for a quiz (participant scores, answers, etc.)
     * Returns the number of participants cleared
     */
    public static function clearLeaderboard(int $quizId): int
    {
        try {
            $deletedCount = 0;

            // Get all participant score keys to find participant IDs
            $scorePattern = "quiz:{$quizId}:participant:*:score";
            $scoreKeys = Redis::keys($scorePattern);

            $participantIds = [];
            foreach ($scoreKeys as $scoreKey) {
                // Extract participant ID from the score key
                preg_match('/participant:([^:]+):score/', $scoreKey, $matches);
                if (isset($matches[1])) {
                    $participantIds[] = $matches[1];
                }
            }

            // Delete all keys for each participant
            foreach ($participantIds as $participantId) {
                // Delete participant info
                $infoKey = "quiz:{$quizId}:participant:{$participantId}";
                Redis::del($infoKey);
                $deletedCount++;

                // Delete score
                $scoreKey = "quiz:{$quizId}:participant:{$participantId}:score";
                Redis::del($scoreKey);
                
                // Delete question order
                $orderKey = "quiz:{$quizId}:participant:{$participantId}:question_order";
                Redis::del($orderKey);
                
                // Delete email participation record
                $participantData = Redis::get($infoKey);
                if ($participantData) {
                    $participant = json_decode($participantData, true);
                    if (isset($participant['email'])) {
                        $emailKey = "quiz:{$quizId}:email:" . md5(strtolower($participant['email']));
                        Redis::del($emailKey);
                    }
                }

                // Delete all question answers
                $answerPattern = "quiz:{$quizId}:participant:{$participantId}:q:*:answer";
                $answerKeys = Redis::keys($answerPattern);
                foreach ($answerKeys as $key) {
                    Redis::del($key);
                }

                // Delete all question start times
                $startPattern = "quiz:{$quizId}:participant:{$participantId}:q:*:start_time";
                $startKeys = Redis::keys($startPattern);
                foreach ($startKeys as $key) {
                    Redis::del($key);
                }
            }

            Log::info('Cleared leaderboard data from Redis', [
                'quiz_id' => $quizId,
                'participants_cleared' => count($participantIds)
            ]);

            return count($participantIds);
        } catch (\Throwable $e) {
            Log::warning('Failed to clear leaderboard data from Redis', [
                'quiz_id' => $quizId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }


    public function getScore(int $quizId, int $participantId): int
    {
        try {
            $scoreKey = "quiz:{$quizId}:participant:{$participantId}:score";
            $score = Redis::get($scoreKey);
            return $score !== null ? (int) $score : 0;
        } catch (\Throwable $e) {
            Log::warning('Failed to get score from Redis', [
                'quiz_id' => $quizId,
                'participant_id' => $participantId,
                'error' => $e->getMessage(),
            ]);
            return 0;
        }
    }
}
