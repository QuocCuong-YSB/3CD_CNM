<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

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

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'quantity_sold' => 'integer',
        'status' => 'integer',
        'sale' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'quantity_sold' => 0,
    ];

    public static $rules = [
        'id_category' => 'required|exists:categories,id',
        'id_brand' => 'required|exists:brands,id',
        'name' => 'required|string|max:255',
        'price' => 'required|numeric|min:0',
        'quantity' => 'required|integer|min:0',
        'sale' => 'nullable|numeric|min:0|max:100',
        'status' => 'nullable|integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'id_category');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'id_brand');
    }

    public static function checkCategory($id_category)
    {
        $error = [];
        $category = Category::find($id_category);

        if (!$category) {
            $error['category'] = 'Product not found!';
        }

        return $error;
    }

    public static function checkBrand($id_brand)
    {
        $error = [];
        $brand = Brand::find($id_brand);

        if (!$brand) {
            $error['brand'] = 'Brand not found!';
        }

        return $error;
    }

    public static function createProduct(array $data)
    {
        return self::create($data);
    }

    public static function getProduct($page = 1, $limit = 1000)
    {
        $skip = ($page - 1) * $limit;

        $data = self::skip($skip)
            ->limit($limit)
            ->with(['brand:id,name', 'category:id,name'])
            ->get();

        $total = self::count();

        return [
            'data' => $data,
            'total' => $total
        ];
    }

    public static function trash()
    {
        return self::onlyTrashed()
            ->with(['brand:id,name', 'category:id,name'])
            ->get();
    }

    public static function countTrashProduct()
    {
        return self::onlyTrashed()->count();
    }

    public static function getProductById($id)
    {
        return self::with(['brand:id,name', 'category:id,name'])
            ->find($id);
    }

    public static function deleteProduct($id)
    {
        $product = self::find($id);
        return $product ? $product->delete() : null;
    }

    public static function deleteMany(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    public static function restoreById($id)
    {
        $product = self::onlyTrashed()->find($id);
        return $product ? $product->restore() : null;
    }

    public static function forceDeleteProduct($id)
    {
        $product = self::withTrashed()->find($id);
        return $product ? $product->forceDelete() : null;
    }

    public static function updateProduct($id, array $data)
    {
        $product = self::find($id);

        if ($product) {
            $product->update($data);
            return $product->fresh(); // Reload from database
        }

        return null;
    }

    public static function getProductCart(array $ids)
    {
        return self::whereIn('id', $ids)->get();
    }

    public static function searchProduct($name)
    {
        return self::where('name', 'LIKE', "%{$name}%")->get();
    }

    public static function getProductByCategory($id_category)
    {
        return self::where('id_category', $id_category)
            ->with(['brand:id,name', 'category:id,name'])
            ->get();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('id_category', $categoryId);
    }

    public function scopeByBrand($query, $brandId)
    {
        return $query->where('id_brand', $brandId);
    }

    public function scopeOnSale($query)
    {
        return $query->where('sale', '>', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('quantity', '>', 0);
    }

    public function getFinalPriceAttribute()
    {
        if ($this->sale > 0) {
            return $this->price * (1 - $this->sale / 100);
        }
        return $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->sale > 0;
    }

    public function getIsInStockAttribute()
    {
        return $this->quantity > 0;
    }
}
