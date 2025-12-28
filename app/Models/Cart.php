<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carts';

    protected $fillable = [
        'id_user',
        'id_product',
        'price',
        'quantity',
        'qualty', 
        'status',
        'orderCode',
        'paymentMethod',
        'address',
        'note',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'qualty' => 'integer',
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => 0, 
        'paymentMethod' => 'cod', 
    ];

    public static $rules = [
        'id_user' => 'required|exists:users,id',
        'id_product' => 'required|exists:products,id',
        'price' => 'nullable|numeric|min:0',
        'quantity' => 'nullable|integer|min:1',
        'status' => 'required|integer|in:0,1,2',
        'paymentMethod' => 'nullable|string|in:cod,paypal',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'id_product');
    }

    public static function createHistory(array $data)
    {
        return self::create($data);
    }

    public static function checkUser($id_user)
    {
        $error = [];
        $user = User::find($id_user);

        if (!$user) {
            $error['user'] = 'User not found!';
        }

        return $error;
    }

    public static function getOrdersByUser($id_user)
    {
        return self::where('id_user', $id_user)
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public static function getOrderById($id)
    {
        return self::with(['product', 'user'])->find($id);
    }

    public static function updateOrderStatus($id, $status)
    {
        $cart = self::find($id);

        if ($cart) {
            $cart->status = $status;
            $cart->save();
            $cart->load('product');
            return $cart;
        }

        return null;
    }

    public static function generateOrderCode()
    {
        $timestamp = now()->timestamp;
        $random = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 6));
        $random2 = strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 4));

        return "ORD{$timestamp}{$random}{$random2}";
    }

    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeWithPaymentMethod($query, $method)
    {
        return $query->where('paymentMethod', $method);
    }
}
