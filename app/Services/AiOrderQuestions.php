<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiOrderQuestions
{
    protected $apiKey;
    protected $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
    }

    public function process(string $text, int $quizId)
    {
        if (!$this->apiKey) {
            throw new \Exception("Gemini API Key is missing.");
        }

        $prompt = "You are a helpful assistant that extracts multiple choice questions from text. 
        Analyze the following text and extract all the multiple choice questions found within it.
        Return ONLY a raw JSON array of objects. Do not wrap the JSON in markdown code blocks.
        
        Each object in the array must have the following keys:
        - \"question\": The question text.
        - \"options\": An array of exactly 4 strings for the options.
        - \"correct_answer\": A single uppercase letter string ('A', 'B', 'C', or 'D') corresponding to the correct option index (A=0, B=1, C=2, D=3).
        - \"time_limit\": The time limit for the question in seconds (integer). if specified in text extract it else default to 25.
        
        Text to analyze:
        $text";

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$this->baseUrl}?key={$this->apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ]
            ]);

            if ($response->failed()) {
                Log::error('Gemini API Error', ['response' => $response->body()]);
                throw new \Exception("Failed to communicate with AI Service.");
            }

            $data = $response->json();
            
            // Extract the generated text
            $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Clean up markdown code blocks if present (just in case)
            $generatedText = preg_replace('/^```json\s*|```\s*$/', '', trim($generatedText));

            $questionsData = json_decode($generatedText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('JSON Decode Error', ['error' => json_last_error_msg(), 'text' => $generatedText]);
                throw new \Exception("Failed to parse AI response.");
            }

            if (!is_array($questionsData)) {
                 // Try to see if it's a single object and wrap it
                 $questionsData = [$questionsData];
            }

            $savedCount = 0;
            foreach ($questionsData as $qData) {
                if (!isset($qData['question'], $qData['options'], $qData['correct_answer']) || count($qData['options']) < 4) {
                    continue; // Skip invalid entries
                }

                // Map A, B, C, D to a, b, c, d for DB storage if needed. 
                // Based on standard, let's assume DB uses 'a', 'b', 'c', 'd' or '1', '2', '3', '4'.
                // Checking the view: $question->correct_option === 'a'
                // So DB uses lowercase 'a', 'b', 'c', 'd'.

                $correctOption = strtolower($qData['correct_answer']);
                if (!in_array($correctOption, ['a', 'b', 'c', 'd'])) {
                     // Auto-fix if it's 0, 1, 2, 3 or something else?
                     // Let's assume the AI follows instructions, but fallback just in case?
                     // Verify against options array? 
                     // For now trust the prompt but sanitize.
                     $correctOption = 'a'; // Default fallback or skip?
                }
                
                $timeLimit = isset($qData['time_limit']) ? (int)$qData['time_limit'] : 25;
                if ($timeLimit <= 0) $timeLimit = 25;

                Question::create([
                    'quiz_id' => $quizId,
                    'question' => $qData['question'],
                    'option_a' => $qData['options'][0],
                    'option_b' => $qData['options'][1],
                    'option_c' => $qData['options'][2],
                    'option_d' => $qData['options'][3],
                    'correct_option' => $correctOption,
                    'score' => 1, // Default score
                    'time_limit' => $timeLimit,
                ]);
                $savedCount++;
            }

            return $savedCount;

        } catch (\Exception $e) {
            Log::error('AiOrderQuestions Service Error: ' . $e->getMessage());
            throw $e;
        }
    }
}
