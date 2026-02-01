<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image',
        'sort_order',
    ];

    /**
     * Get the event that owns the image.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
