<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DynamicFormSubmission extends Model
{
    protected $fillable = [
        'dynamic_form_id',
        'data',
        'is_payed',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the form that owns this submission.
     */
    public function dynamicForm(): BelongsTo
    {
        return $this->belongsTo(DynamicForm::class);
    }
}
