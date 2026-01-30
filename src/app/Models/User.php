<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Like;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * 一括代入を許可するカラム
     */
    protected $fillable = [
        'name',
        'email',
        'password',

        // ===== プロフィール用 =====
        'profile_image', // 画像パス（例: profiles/xxxx.jpg）
        'postal_code',
        'address',
        'building',
    ];

    /**
     * JSONに出さない（Fortifyの2FAも含める）
     */
    protected $hidden = [
        'password',
        'remember_token',

        // Fortify（2FA）を使う場合に必要（使わなくても入っててOK）
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * 型キャスト
     */
    protected $casts = [
        'email_verified_at' => 'datetime',

        // Fortifyで2FAを使う場合（使わなくても害なし）
        'two_factor_confirmed_at' => 'datetime',
    ];

    /**
     * 出品した商品
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    /**
     * 購入履歴
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * いいね（likes）
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * いいねした商品一覧（マイページで使える）
     */
    public function likedItems()
    {
        return $this->belongsToMany(Item::class, 'likes')
            ->withTimestamps();
    }

    /**
     * プロフィール画像URL（ビューで使いやすく）
     */
    public function getProfileImageUrlAttribute(): string
    {
        if (!empty($this->profile_image)) {
            return asset('storage/' . $this->profile_image);
        }

        return 'https://via.placeholder.com/150/dcdcdc/333?text=No+Image';
    }
}