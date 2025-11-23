<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'board_id',
        'highboard_id',
        'committee_id',
        'title',
        'content',
        'youtube_video_id',
        'tags',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the board that owns the lesson.
     */
    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    /**
     * Get the highboard that owns the lesson.
     */
    public function highboard()
    {
        return $this->belongsTo(Highboard::class);
    }

    /**
     * Get the committee that owns the lesson.
     */
    public function committee()
    {
        return $this->belongsTo(Committee::class);
    }

    /**
     * Get the attachments for the lesson.
     */
    public function attachments()
    {
        return $this->hasMany(LessonAttachment::class);
    }

    /**
     * Extract YouTube video ID from URL.
     * Supports formats:
     * - https://www.youtube.com/watch?v=VIDEO_ID
     * - https://youtu.be/VIDEO_ID
     * - https://www.youtube.com/embed/VIDEO_ID
     */
    public static function extractYoutubeId($url)
    {
        if (empty($url)) {
            return null;
        }

        // Pattern to match various YouTube URL formats
        $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
        
        if (preg_match($pattern, $url, $matches)) {
            return $matches[1];
        }

        // If it's already just an ID (11 characters)
        if (strlen($url) === 11 && preg_match('/^[a-zA-Z0-9_-]{11}$/', $url)) {
            return $url;
        }

        return null;
    }

    /**
     * Extract URLs from content and return as array.
     */
    public static function extractLinks($content)
    {
        if (empty($content)) {
            return [];
        }

        preg_match_all(
            '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#',
            $content,
            $matches
        );

        return array_unique($matches[0]);
    }

    /**
     * Scope a query to only include active lessons.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
