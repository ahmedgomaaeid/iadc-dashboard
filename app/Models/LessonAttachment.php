<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LessonAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    /**
     * Get the lesson that owns the attachment.
     */
    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }

    /**
     * Get icon class based on file type.
     */
    public function getIconAttribute()
    {
        $extension = strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
        
        $icons = [
            'pdf' => 'fe fe-file-text text-danger',
            'doc' => 'fe fe-file-text text-primary',
            'docx' => 'fe fe-file-text text-primary',
            'xls' => 'fe fe-file-text text-success',
            'xlsx' => 'fe fe-file-text text-success',
            'ppt' => 'fe fe-file-text text-warning',
            'pptx' => 'fe fe-file-text text-warning',
            'jpg' => 'fe fe-image text-info',
            'jpeg' => 'fe fe-image text-info',
            'png' => 'fe fe-image text-info',
            'gif' => 'fe fe-image text-info',
            'zip' => 'fe fe-archive text-secondary',
            'rar' => 'fe fe-archive text-secondary',
        ];

        return $icons[$extension] ?? 'fe fe-file text-muted';
    }
}
