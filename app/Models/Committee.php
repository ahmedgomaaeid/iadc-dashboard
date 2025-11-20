<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Committee extends Model
{
    protected $fillable = [
        'name',
        'field_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the field that this committee belongs to
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Scope to filter only active committees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all users belonging to this committee
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all board members belonging to this committee
     */
    public function boards()
    {
        return $this->hasMany(Board::class);
    }
}
