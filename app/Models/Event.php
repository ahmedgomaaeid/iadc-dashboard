<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'description',
        'type',
        'date_from',
        'date_to',
        'place',
        'attendees_number',
        'register_link',
        'register_active',
        'is_active',
    ];

    protected $casts = [
        'date_from' => 'date',
        'date_to' => 'date',
        'register_active' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the partners for the event.
     */
    public function partners()
    {
        return $this->hasMany(EventPartner::class);
    }

    /**
     * Get the gallery images for the event.
     */
    public function images()
    {
        return $this->hasMany(EventImage::class)->orderBy('sort_order');
    }

    /**
     * Scope a query to only include active events.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include events (not visits).
     */
    public function scopeEvents($query)
    {
        return $query->where('type', 'event');
    }

    /**
     * Scope a query to only include visits.
     */
    public function scopeVisits($query)
    {
        return $query->where('type', 'visit');
    }

    /**
     * Check if the event is an event type.
     */
    public function isEvent()
    {
        return $this->type === 'event';
    }

    /**
     * Check if the event is a visit type.
     */
    public function isVisit()
    {
        return $this->type === 'visit';
    }
}
