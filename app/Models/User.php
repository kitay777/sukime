<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;




// リレーション
public function favorite()
{
    return $this->belongsTo(User::class, 'favorite_user_id');
}
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password',
        'real_name', 'school_name', 'grade', 'class_or_department',
        'profile_completed',
        'favorite_user_id', 'favorite_set_at',
        'gender',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'profile_completed' => 'boolean',
        'favorite_set_at'   => 'datetime',
        'initial_questions_completed' => 'boolean',
    ];


    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getGenderLabelAttribute(): string
    {
        return match($this->gender) {
            'male'   => '男性',
            'female' => '女性',
            'other'  => 'その他',
            default  => '未回答',
        };
    }
}
