<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    protected static function booted()
    {
        static::saving(function ($cart) {
            if ($cart->quantity < 1) {
                throw new \Exception('Quantity must be at least 1');
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOfUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getSubtotalAttribute()
    {
        return $this->quantity * ($this->product->price ?? 0);
    }
}
