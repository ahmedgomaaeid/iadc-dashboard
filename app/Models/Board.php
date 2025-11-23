<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Board extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'field_id',
        'committee_id',
        'is_active',
        'image',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the committee that this board member belongs to
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the field that this board member belongs to
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Scope to filter only active board members
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get all members (users) in the board's committee.
     */
    public function members()
    {
        return $this->committee->users();
    }

    /**
     * Get all lessons created by this board member.
     */
    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * Get all tasks created by this board member.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
