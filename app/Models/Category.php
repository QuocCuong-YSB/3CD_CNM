<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'name' => 'required|string|max:255',
    ];

    /**
     * Get products belonging to this category
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'id_category');
    }

    /**
     * Create a new category
     *
     * @param array $data
     * @return Category
     */
    public static function createCategory(array $data)
    {
        return self::create($data);
    }

    /**
     * Get all categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getCategory()
    {
        return self::all();
    }

    /**
     * Delete a category (soft delete)
     *
     * @param int $id
     * @return bool|null
     */
    public static function deleteCategory($id)
    {
        $category = self::find($id);
        return $category ? $category->delete() : null;
    }

    /**
     * Update a category
     *
     * @param int $id
     * @param array $data
     * @return Category|null
     */
    public static function updateCategory($id, array $data)
    {
        $category = self::find($id);
        if ($category) {
            $category->update($data);
            return $category->fresh();
        }
        return null;
    }

    /**
     * Get trashed (soft deleted) categories
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTrash()
    {
        return self::onlyTrashed()->get();
    }

    /**
     * Count trashed categories
     *
     * @return int
     */
    public static function countTrash()
    {
        return self::onlyTrashed()->count();
    }

    /**
     * Restore a soft deleted category
     *
     * @param int $id
     * @return bool|null
     */
    public static function restoreCategory($id)
    {
        $category = self::onlyTrashed()->find($id);
        return $category ? $category->restore() : null;
    }

    /**
     * Permanently delete a category
     *
     * @param int $id
     * @return bool|null
     */
    public static function forceDeleteCategory($id)
    {
        $category = self::withTrashed()->find($id);
        return $category ? $category->forceDelete() : null;
    }

    /**
     * Delete multiple categories
     *
     * @param array $ids
     * @return int
     */
    public static function deleteManyCategories(array $ids)
    {
        return self::whereIn('id', $ids)->delete();
    }

    /**
     * Get category by ID
     *
     * @param int $id
     * @return Category|null
     */
    public static function getCategoryById($id)
    {
        return self::find($id);
    }

    /**
     * Search categories by name
     *
     * @param string $name
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function searchCategory($name)
    {
        return self::where('name', 'LIKE', "%{$name}%")->get();
    }

    /**
     * Scope to search by name
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'LIKE', "%{$search}%");
    }

    /**
     * Get categories with product count
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function withProductCount()
    {
        return self::withCount('products')->get();
    }
}
