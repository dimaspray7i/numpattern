<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    protected $fillable = [
        'user_id',
        'current_score',
        'lives',
        'correct_streak',
        'wrong_streak',
        'difficulty',
        'current_answer',
    ];

    protected $casts = [
        'current_score'  => 'integer',
        'lives'          => 'integer',
        'correct_streak' => 'integer',
        'wrong_streak'   => 'integer',
        'current_answer' => 'integer',
    ];

    protected $hidden = ['current_answer']; // Never expose to API output

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
