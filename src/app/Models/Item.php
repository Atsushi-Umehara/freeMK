<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// ★ リレーション用
use App\Models\User;
use App\Models\Purchase;
use App\Models\Comment;
use App\Models\Like;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'status',
        'image_path',
        'category',
        'condition',
        'brand',
    ];

    protected $casts = [
        'price' => 'integer',
    ];

    /**
     * 出品者
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 購入履歴（基本は1件想定でも hasMany でOK）
     */
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * コメント
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * いいね（Like）
     * likes テーブルを使っている前提（item_id / user_id）
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * 売り切れ判定（$item->is_sold で使える）
     */
    public function getIsSoldAttribute(): bool
    {
        return ($this->status ?? '') === 'sold';
    }

    /**
     * 画像URL（$item->image_url で使える）
     */
    public function getImageUrlAttribute(): string
    {
        return !empty($this->image_path)
            ? asset('storage/' . $this->image_path)
            : 'https://via.placeholder.com/600x600/dcdcdc/333?text=%E5%95%86%E5%93%81%E7%94%BB%E5%83%8F';
    }
}