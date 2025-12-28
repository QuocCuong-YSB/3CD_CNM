<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'brands';

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static $rules = [
        'name' => 'required|string|max:255',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'id_brand');
    }

    public static function createBrand(array $data)
    {
        return self::create($data);
    }

    public static function getBrand()
    {
        return self::all();
    }

    public static function deleteBrand($id)
    {
        $brand = self::find($id);
        return $brand ? $brand->delete() : null;
    }

    public static function updateBrand($id, array $data)
    {
        $brand = self::find($id);
        if ($brand) {
            $brand->update($data);
            return $brand->fresh();
        }
        return null;
    }

    public static function getTrash()
    {
        return self::onlyTrashed()->get();
    }

    public static function countTrash()
    {
        return self::onlyTrashed()->count();
    }

    public static function restoreBrand($id)
    {
        $brand = self::onlyTrashed()->find($id);
        return $brand ? $brand->restore() : null;
    }

    public static function forceDeleteBrand($id)
    {
        $brand = self::withTrashed()->find($id);
        return $brand ? $brand->forceDelete() : null;
    }

    public static function deleteManyBrands(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    public static function getBrandById($id)
    {
        return self::find($id);
    }

    public static function searchBrand($name)
    {
        return self::where('name', 'LIKE', "%{$name}%")->get();
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    public static function withProductCount()
    {
        return self::withCount('products')->get();
    }

    public static function getBrandWithProducts($id)
    {
        return self::with('products')->find($id);
    }
}
