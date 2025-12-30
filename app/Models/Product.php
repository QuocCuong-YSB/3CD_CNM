<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $casts = [
        'image' => 'array',
    ];
    protected $fillable = [
        'id_category',
        'id_brand',
        'name',
        'image',
        'price',
        'status',
        'sale',
        'detail',
        'quantity',
        'quantity_sold',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brand');
    }
}
