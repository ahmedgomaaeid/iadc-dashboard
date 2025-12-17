<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventPartner extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'image',
        'type',
    ];

    /**
     * Partner types available.
     */
    public const TYPES = [
        'main' => 'Main',
        'diamond' => 'Diamond',
        'platinum' => 'Platinum',
        'golden' => 'Golden',
        'silver' => 'Silver',
        'technical' => 'Technical',
        'catering' => 'Catering',
        'transportation' => 'Transportation',
        'printing' => 'Printing',
    ];

    /**
     * Get the event that owns the partner.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the display name for the partner type.
     */
    public function getTypeNameAttribute()
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }
}
