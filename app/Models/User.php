<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'is_active',
        'image',
        'university',
        'faculty',
        'academic_year',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the committees that this user belongs to
     */
    public function committees()
    {
        return $this->belongsToMany(Committee::class);
    }

    /**
     * Get the fields that this user belongs to (through committees)
     */
    public function fields()
    {
        return Field::whereIn('id', $this->committees()->pluck('field_id'))->get();
    }

    /**
     * Scope to filter only active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }
}
