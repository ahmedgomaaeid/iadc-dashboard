<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'name',
        'url',
    ];

    /**
     * Get the event that owns the link.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
