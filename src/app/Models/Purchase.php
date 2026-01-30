<?php

namespace App\Models;

use App\Models\Item;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    /*
    |--------------------------------------------------------------------------
    | Status / Method constants
    |--------------------------------------------------------------------------
    */

    // payment_status
    public const STATUS_PENDING  = 'pending';
    public const STATUS_PAID     = 'paid';
    public const STATUS_FAILED   = 'failed';
    public const STATUS_CANCELED = 'canceled';

    // payment_method
    public const METHOD_CREDIT      = 'credit';       // Stripe（カード）
    public const METHOD_CONVENIENCE = 'convenience';  // コンビニ（擬似購入/別処理）

    /*
    |--------------------------------------------------------------------------
    | Mass assignment
    |--------------------------------------------------------------------------
    */
    protected $fillable = [
        'user_id',
        'item_id',
        'price',
        'postal_code',
        'address',
        'name',
        'payment_method',

        // Stripe
        'stripe_session_id',
        'payment_status',
        'stripe_payment_intent_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Casts
    |--------------------------------------------------------------------------
    */
    protected $casts = [
        'user_id' => 'integer',
        'item_id' => 'integer',
        'price'   => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsPaidAttribute(): bool
    {
        return ($this->payment_status ?? '') === self::STATUS_PAID;
    }

    public function getIsPendingAttribute(): bool
    {
        return ($this->payment_status ?? '') === self::STATUS_PENDING;
    }

    public function getIsCreditAttribute(): bool
    {
        return ($this->payment_method ?? '') === self::METHOD_CREDIT;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PAID);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('payment_status', self::STATUS_PENDING);
    }
}