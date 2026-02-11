<?php

namespace App\Observers;

use App\Models\Question;
use App\Models\Quiz;
use App\Models\CommitteeQuizStat;

class QuestionObserver
{
    /**
     * Handle the Question "created" event.
     */
    public function created(Question $question): void
    {
        $this->updateCommitteeStats($question, 1);
    }

    /**
     * Handle the Question "updated" event.
     */
    public function updated(Question $question): void
    {
        // If quiz_id changed (unlikely but possible), we need to handle it.
        if ($question->isDirty('quiz_id')) {
            $originalQuizId = $question->getOriginal('quiz_id');
            $originalQuiz = Quiz::find($originalQuizId);
            if ($originalQuiz && $originalQuiz->committee_id) {
                 $this->incrementStat($originalQuiz->committee_id, -1);
            }
            
            $this->updateCommitteeStats($question, 1);
        }
    }

    /**
     * Handle the Question "deleted" event.
     */
    public function deleted(Question $question): void
    {
        $this->updateCommitteeStats($question, -1);
    }

    /**
     * Handle the Question "restored" event.
     */
    public function restored(Question $question): void
    {
        $this->updateCommitteeStats($question, 1);
    }

    /**
     * Handle the Question "force deleted" event.
     */
    public function forceDeleted(Question $question): void
    {
        $this->updateCommitteeStats($question, -1);
    }

    private function updateCommitteeStats(Question $question, int $change)
    {
        $quiz = $question->quiz; // Assuming belongsTo relationship exists and is loaded or fetched
        if (!$quiz) {
            $quiz = Quiz::find($question->quiz_id);
        }

        if ($quiz && $quiz->committee_id && $quiz->visibility === 'private') {
            $this->incrementStat($quiz->committee_id, $change);
        }
    }

    private function incrementStat($committeeId, $amount)
    {
        $stats = CommitteeQuizStat::firstOrCreate(
            ['committee_id' => $committeeId],
            ['total_questions' => 0]
        );
        
        $stats->total_questions += $amount;
        if ($stats->total_questions < 0) $stats->total_questions = 0; // Helper just in case
        $stats->save();
    }
}
