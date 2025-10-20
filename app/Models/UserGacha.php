<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGacha extends Model
{
    protected $fillable = [
        'user_id',
        'gacha_date',
        'rarity',
        'is_win',
    ];

    protected $casts = [
        'gacha_date' => 'date',
        'is_win' => 'boolean',
    ];
}
