<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model
{
    protected $fillable = ['user_id','amount','type','reason','meta'];
    protected $casts = ['meta' => 'array'];
}
