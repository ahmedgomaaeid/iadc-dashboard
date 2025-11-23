<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function getFormattedSizeAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getIconAttribute()
    {
        $extension = pathinfo($this->file_name, PATHINFO_EXTENSION);
        
        return match(strtolower($extension)) {
            'pdf' => 'fe fe-file-text',
            'doc', 'docx' => 'fe fe-file-word',
            'xls', 'xlsx' => 'fe fe-file-plus',
            'ppt', 'pptx' => 'fe fe-monitor',
            'jpg', 'jpeg', 'png', 'gif' => 'fe fe-image',
            'zip', 'rar' => 'fe fe-package',
            default => 'fe fe-file',
        };
    }
}
