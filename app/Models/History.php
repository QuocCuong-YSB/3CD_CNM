<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class History extends Model
{
    use HasFactory, SoftDeletes;

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

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public static function generateOrderCode()
    {
        return 'ORD-' . strtoupper(uniqid());
    }
}
