<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'answer',
        'answered_at',
    ];

    protected $casts = [
        'answered_at' => 'datetime',
    ];
}
