<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Contest extends Model
{
    /** @use HasFactory<\Database\Factories\ContestFactory> */
    use HasFactory;

    protected $guarded = ['id'];
    public function questions(): HasMany
    {
        return $this->hasMany(Question::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class);
    }

    public function prize() : HasOne
    {
        return $this->hasOne(Prize::class);
    }
    public function isVip(): bool
    {
        return $this->access_level === 'vip';
    }

}
