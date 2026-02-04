<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FrameCategory
 *
 * @property int $id
 * @property int $parent_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property string $thumb
 * @property int $sequence_number
 * @property int|null $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FrameItem> $frameItem
 * @property-read int|null $frame_item_count
 * @property-read FrameCategory|null $parentCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FrameCategory> $subcategories
 * @property-read int|null $subcategories_count
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FrameCategory extends Model
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

    public function frameItem()
    {
        return $this->hasMany(FrameItem::class);
    }
    public function subcategories()
    {
        return $this->hasMany(FrameCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(FrameCategory::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = FrameCategory::where('parent_category_id',0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = FrameCategory::where('id',$category)->get();
        if ( empty($categories->toArray()) ) {
            $categories = FrameCategory::where('id_name',$category)->get();
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
