<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManagementEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_type',
        'user_id',
        'committee_id',
        'type',
        'score',
        'related_type',
        'related_id',
    ];

    public function user()
    {
        return $this->morphTo();
    }

    public function related()
    {
        return $this->morphTo();
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
