<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'name',
        'url',
    ];

    /**
     * Get the article that owns the link.
     */
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
