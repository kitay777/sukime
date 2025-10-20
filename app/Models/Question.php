<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'content',
        'rarity',
        'is_active',
        'sort_order',
    ];
    public function answers()
    {
        return $this->hasMany(UserQuestionAnswer::class);
    }
}
