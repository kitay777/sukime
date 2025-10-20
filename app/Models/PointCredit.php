<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointCredit extends Model
{
    protected $fillable = ['payment_intent_id','user_id','amount_points'];
}
