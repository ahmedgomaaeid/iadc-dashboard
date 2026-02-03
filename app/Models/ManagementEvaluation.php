<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'committee_id',
        'type',
        'score',
        'related_type',
        'related_id',
    ];

    /**
     * Get the user/member that owns the evaluation.
     */
    public function user()
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
     * Get the owning related model (session, etc.).
     */
    public function related()
    {
        return $this->morphTo();
    }
}
