<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Crush extends Model
{
    protected $fillable = [
    'user_id','name','school','faculty','grade','gender','last_changed_at','crush_user_id',
    ];

    protected $casts = [
        'last_changed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
