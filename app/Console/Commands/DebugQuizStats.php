<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Committee;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\CommitteeQuizStat;
use App\Models\UserEvaluation;

class DebugQuizStats extends Command
{
    protected $signature = 'debug:quiz-stats';
    protected $description = 'Debug Quiz Stats';

    public function handle()
    {
        $committees = Committee::all();

        foreach ($committees as $committee) {
            $this->info("Committee: {$committee->name} (ID: {$committee->id})");
            
            // 2. Check Quiz Stats from DB
            $stat = CommitteeQuizStat::where('committee_id', $committee->id)->first();
            $this->line("  -> Stored Total Questions (Private): " . ($stat ? $stat->total_questions : 'NULL'));
            
            // 3. Manual Count of Private Questions
            $manualCount = Question::whereHas('quiz', function($q) use ($committee) {
                $q->where('committee_id', $committee->id)
                  ->where('visibility', 'private');
            })->count();
            $this->line("  -> Actual Private Questions Count: {$manualCount}");
            
            // 4. Check Public Questions Count (for context)
            $publicCount = Question::whereHas('quiz', function($q) use ($committee) {
                $q->where('committee_id', $committee->id)
                  ->where('visibility', '!=', 'private');
            })->count();
            $this->line("  -> Public Questions Count: {$publicCount}");
            
            // 5. Check User Evaluations
            $evals = UserEvaluation::where('committee_id', $committee->id)
                ->where('type', 'quiz')
                ->get();
                
            $this->line("  -> User Evaluations Count: " . $evals->count());
            
            $usersWithEvals = $evals->unique('user_id');
            foreach($usersWithEvals as $eval) {
                // Get total score for this user
                $score = UserEvaluation::where('committee_id', $committee->id)
                    ->where('user_id', $eval->user_id)
                    ->where('type', 'quiz')
                    ->sum('score');
                
                $this->line("     User ID {$eval->user_id}: Total Score {$score}");
            }
            $this->line("--------------------------------------------------");
        }
    }
}
