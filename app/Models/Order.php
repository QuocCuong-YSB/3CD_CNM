<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_code',
        'total_amount',
        'payment_method',
        'status',
        'name',
        'email',
        'phone',
        'address',
        'note',
        'eco_tax',
        'discount',
        'voucher_code',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate a unique order code
     */
    public static function generateOrderCode()
    {
        $timestamp = now()->timestamp;
        $random = strtoupper(substr(md5(uniqid()), 0, 6));
        return "ORD-{$timestamp}-{$random}";
    }
}
