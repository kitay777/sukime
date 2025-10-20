<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TweetUnlock extends Model
{
    use HasFactory;

    protected $table = 'tweet_unlocks';

    protected $fillable = [
        'tweet_id', 'buyer_id', 'price_points',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }
}
