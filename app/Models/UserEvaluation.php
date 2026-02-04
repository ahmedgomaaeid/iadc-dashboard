<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'evaluator_type',
        'evaluator_id',
        'committee_id',
        'type',
        'score',
        'max_score',
        'related_type',
        'related_id',
        'evaluation_date',
        'event_name',
    ];

    /**
     * Get the user that owns the evaluation.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the evaluator (user or board).
     */
    public function evaluator()
    {
        return $this->morphTo();
    }

    /**
     * Get the committee related to the evaluation.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the owning related model (session, quiz, etc.).
     */
    public function related()
    {
        return $this->morphTo();
    }
}
