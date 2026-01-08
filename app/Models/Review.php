<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'order_code',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function order()
    {
        return $this->belongsTo(History::class);
    }

    public static function addReview(array $data)
    {
        return self::create($data);
    }

    public static function getReviewsByProduct($productId)
    {
        return self::with('user:id,name,avatar')
            ->where('product_id', $productId)
            ->latest()
            ->get();
    }
}
