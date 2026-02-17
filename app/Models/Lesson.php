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
     * Extract Video ID from URL (YouTube or Google Drive).
     * Returns:
     * - YouTube ID (11 chars)
     * - "drive:{FILE_ID}" for Google Drive
     */
    public static function extractVideoId($url)
    {
        if (empty($url)) {
            return null;
        }

        // Try YouTube first
        $youtubeId = self::extractYoutubeId($url);
        if ($youtubeId) {
            return $youtubeId;
        }

        // Try Google Drive
        // Patterns:
        // https://drive.google.com/file/d/FILE_ID/view
        // https://drive.google.com/open?id=FILE_ID
        // drive.google.com/file/d/FILE_ID
        
        $pattern = '/(?:drive\.google\.com\/(?:file\/d\/|open\?id=)|Docs\/)([-\w]+)/i';
        if (preg_match($pattern, $url, $matches)) {
            return 'drive:' . $matches[1];
        }

        return null;
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
     * Get video type: 'youtube' or 'drive' or null
     */
    public function getVideoTypeAttribute()
    {
        if (empty($this->youtube_video_id)) {
            return null;
        }

        if (str_starts_with($this->youtube_video_id, 'drive:')) {
            return 'drive';
        }

        return 'youtube';
    }

    /**
     * Get the video embed ID (without prefix)
     */
    public function getVideoEmbedIdAttribute()
    {
        if (empty($this->youtube_video_id)) {
            return null;
        }

        if (str_starts_with($this->youtube_video_id, 'drive:')) {
            return substr($this->youtube_video_id, 6);
        }

        return $this->youtube_video_id;
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
