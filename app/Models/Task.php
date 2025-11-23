<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'committee_id',
        'title',
        'content',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    public function attachments()
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /**
     * Extract links from content and return as array
     */
    public static function extractLinks($content)
    {
        preg_match_all('/https?:\/\/[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/))/', $content, $matches);
        return array_unique($matches[0]);
    }
}
