<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all committees belonging to this field
     */
    public function committees()
    {
        return $this->hasMany(Committee::class);
    }

    /**
     * Scope to filter only active fields
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
