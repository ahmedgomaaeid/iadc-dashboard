<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommitteeQuizStat extends Model
{
    use HasFactory;

    protected $fillable = ['committee_id', 'total_questions'];

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }
}
