<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categories';

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
        return $this->hasMany(Product::class, 'id_category');
    }
    
    public static function createCategory(array $data)
    {
        return self::create($data);
    }

    public static function getCategory()
    {
        return self::all();
    }

    public static function deleteCategory($id)
    {
        $category = self::find($id);
        return $category ? $category->delete() : null;
    }

    public static function updateCategory($id, array $data)
    {
        $category = self::find($id);
        if ($category) {
            $category->update($data);
            return $category->fresh();
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

    public static function restoreCategory($id)
    {
        $category = self::onlyTrashed()->find($id);
        return $category ? $category->restore() : null;
    }

    public static function forceDeleteCategory($id)
    {
        $category = self::withTrashed()->find($id);
        return $category ? $category->forceDelete() : null;
    }

    public static function deleteManyCategories(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    public static function getCategoryById($id)
    {
        return self::find($id);
    }

    public static function searchCategory($name)
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
}
