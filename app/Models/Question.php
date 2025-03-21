<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    /** @use HasFactory<\Database\Factories\QuestionFactory> */
    use HasFactory;

    protected $guarded = ['id'];
    public function contest(): BelongsTo
    {
        return $this->belongsTo(Contest::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }
}
