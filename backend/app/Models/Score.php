<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'score',
        'difficulty',
        'created_at',
    ];

    protected $casts = [
        'score'      => 'integer',
        'created_at' => 'datetime',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->created_at = $model->created_at ?? now();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
