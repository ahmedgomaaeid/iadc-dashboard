<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'google_session_id',
        'rating',
        'message',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function session()
    {
        return $this->belongsTo(GoogleSession::class, 'google_session_id');
    }
}
