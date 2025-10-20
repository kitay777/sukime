<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TweetMedia extends Model
{
    use HasFactory;

    protected $table = 'tweet_media';

    protected $fillable = [
        'tweet_id', 'kind', 'path', 'thumb_path',
        'width', 'height', 'duration', 'sort_order',
    ];

    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }
}
