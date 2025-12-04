<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_active', 'visibility', 'committee_id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    // Scope for global quizzes
    public function scopeGlobal($query)
    {
        return $query->where('visibility', 'global');
    }

    // Scope for private quizzes
    public function scopePrivate($query)
    {
        return $query->where('visibility', 'private');
    }

    // Scope for quizzes of a specific committee
    public function scopeForCommittee($query, $committeeId)
    {
        return $query->where('committee_id', $committeeId);
    }

    // Scope for quizzes accessible by a user (global + user's committees)
    public function scopeAccessibleByUser($query, $user)
    {
        $committeeIds = $user->committees->pluck('id')->toArray();
        
        return $query->where(function ($q) use ($committeeIds) {
            $q->where('visibility', 'global')
              ->orWhere(function ($subQ) use ($committeeIds) {
                  $subQ->where('visibility', 'private')
                       ->whereIn('committee_id', $committeeIds);
              });
        });
    }
}
