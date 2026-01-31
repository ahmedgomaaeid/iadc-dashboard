<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use HasFactory;

    protected $table = 'meeting_sessions';

    protected $fillable = [
        'title',
        'description',
        'start_time',
        'end_time',
        'meeting_link',
        'creator_id',
        'creator_type',
        'committee_id',
        'creator_joined',
        'zoom_meeting_id',
        'zoom_join_url',
        'zoom_start_url',
        'zoom_password',
        'parent_session_id',
        'is_continuation',
        'continuation_count',
        'is_finally_ended',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'creator_joined' => 'boolean',
        'is_continuation' => 'boolean',
        'is_finally_ended' => 'boolean',
    ];

    public function creator()
    {
        return $this->morphTo();
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function parentSession()
    {
        return $this->belongsTo(Session::class, 'parent_session_id');
    }

    public function continuations()
    {
        return $this->hasMany(Session::class, 'parent_session_id');
    }

    /**
     * Get the root session (original session in the chain).
     */
    public function getRootSession()
    {
        $session = $this;
        while ($session->parent_session_id) {
            $session = $session->parentSession;
        }
        return $session;
    }

    /**
     * Get the latest session in the continuation chain.
     */
    public function getLatestContinuation()
    {
        $latest = $this->getRootSession();
        while ($latest->continuations()->exists()) {
            $latest = $latest->continuations()->latest()->first();
        }
        return $latest;
    }
}
