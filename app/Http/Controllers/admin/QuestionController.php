<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\QuizCacheService;

class QuestionController extends Controller
{
    /**
     * Redirect questions index to the quiz page (centralized management).
     */
    public function index(Quiz $quiz)
    {
        return redirect()->route('admin.quizzes.show', $quiz);
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create(Quiz $quiz)
    {
        return view('admin.questions.create', compact('quiz'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Quiz $quiz)
    {
        $request->validate([
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_option' => 'required|in:a,b,c,d',
            'time_limit' => 'required|integer|min:5|max:300',
        ]);

        $quiz->questions()->create($request->all());

        // Keep Redis cache in sync if quiz is active
        if ($quiz->is_active) {
            QuizCacheService::store($quiz->fresh('questions'));
        }

        return redirect()->route('admin.quizzes.show', $quiz)->with('success', 'Question added successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Question $question)
    {
        return view('admin.questions.edit', compact('question'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_option' => 'required|in:a,b,c,d',
            'time_limit' => 'required|integer|min:5|max:300',
        ]);

        $question->update($request->all());

        // Keep Redis cache in sync if quiz is active
        $quiz = $question->quiz()->with('questions')->first();
        if ($quiz && $quiz->is_active) {
            QuizCacheService::store($quiz);
        }

        return redirect()->route('admin.quizzes.show', $question->quiz)->with('success', 'Question updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Question $question)
    {
        $quiz = $question->quiz;
        $question->delete();

        // Keep Redis cache in sync if quiz is active
        if ($quiz && $quiz->is_active) {
            QuizCacheService::store($quiz->fresh('questions'));
        }

        return redirect()->route('admin.quizzes.show', $quiz)->with('success', 'Question deleted successfully.');
    }
}
