<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tweet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'body', 'is_paid', 'price_points',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'price_points' => 'integer',
    ];

    // 投稿者
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // メディア
    public function media()
    {
        return $this->hasMany(TweetMedia::class)->orderBy('sort_order');
    }

    // アンロック（購入）履歴
    public function unlocks()
    {
        return $this->hasMany(TweetUnlock::class);
    }

    // ロック判定（あるユーザーが見れるか）
    public function isUnlockedFor(?int $viewerId): bool
    {
        if (!$this->is_paid) return true;                 // 無料
        if (!$viewerId) return false;
        if ($this->user_id === $viewerId) return true;    // 自分の投稿
        return $this->unlocks()->where('buyer_id', $viewerId)->exists();
    }
}
