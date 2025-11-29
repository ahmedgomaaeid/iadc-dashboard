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
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'creator_joined' => 'boolean',
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
