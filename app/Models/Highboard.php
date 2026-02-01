<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Highboard extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'field_id',
        'is_active',
        'image',
        'zoom_access_token',
        'zoom_refresh_token',
        'zoom_token_expires_at',
        'google_id',
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'google_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'zoom_access_token',
        'zoom_refresh_token',
        'google_access_token',
        'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'zoom_token_expires_at' => 'datetime',
            'google_token_expires_at' => 'datetime',
        ];
    }

    /**
     * Get the field that this highboard member belongs to
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Scope to filter only active highboard members
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all lessons created by this highboard member.
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get all tasks created by this highboard member.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
