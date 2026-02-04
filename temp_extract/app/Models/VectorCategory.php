<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VectorCategory
 *
 * @property int $id
 * @property int $parent_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property string $thumb
 * @property int $sequence_number
 * @property int|null $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read VectorCategory|null $parentCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VectorCategory> $subcategories
 * @property-read int|null $subcategories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VectorItem> $svgItem
 * @property-read int|null $svg_item_count
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VectorCategory extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'parent_category_id',
        'emp_id',
        'name',
        'thumb',
        'sequence_number',
        'status',
    ];

    public function svgItem()
    {
        return $this->hasMany(VectorItem::class);
    }
    public function subcategories()
    {
        return $this->hasMany(VectorCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(VectorCategory::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = VectorCategory::where('parent_category_id', 0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = VectorCategory::where('id', $category)->get();
        if (empty($categories->toArray())) {
            $categories = VectorCategory::where('id_name', $category)->get();
        }

        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }

    protected function getSubcategoriesTree()
    {
        $subcategories = $this->subcategories;
        foreach ($subcategories as $subcategory) {
            $subcategory->subcategories = $subcategory->getSubcategoriesTree();
        }
        return $subcategories;
    }
}
