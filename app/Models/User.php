<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use MustVerifyEmail, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    // 多对多，用户收藏
    public function favorites()
    {
        return $this->belongsToMany(Product::class, 'user_favorites')
            ->withTimestamps()
            ->orderBy('user_favorites.created_at', 'desc');
    }
}
