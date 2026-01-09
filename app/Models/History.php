<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'histories';

    protected $fillable = [
        'id_user',
        'id_product',
        'price',
        'quantity',
        'status',
        'order_code',
        'payment_method',
        'address',
        'note',
    ];

    protected $casts = [
        'status' => 'integer',
        'price'  => 'float',
        'quantity' => 'integer',
    ];

    const STATUS_NEW        = 0;
    const STATUS_PAID       = 1;
    const STATUS_SHIPPING   = 2;
    const STATUS_COMPLETED  = 3; 
    const STATUS_CANCELLED  = 4;

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'order_code', 'order_code');
    }

    public static function generateOrderCode()
    {
        return 'ORD-' . strtoupper(uniqid());
    }

    public static function hasCompletedPurchase($userId, $productId)
    {
        return self::where('id_user', $userId)
            ->where('id_product', $productId)
            ->where('status', self::STATUS_COMPLETED)
            ->exists();
    }

    public static function getCompletedOrder($userId, $productId)
    {
        return self::where('id_user', $userId)
            ->where('id_product', $productId)
            ->where('status', self::STATUS_COMPLETED)
            ->latest()
            ->first();
    }
}
