<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleSession extends Model
{
    protected $table = 'google_sessions';

    protected $fillable = [
        'title',
        'session_url',
        'start_time',
        'end_time',
        'creator_id',
        'creator_type',
        'committee_id',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function creator()
    {
        return $this->morphTo();
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
