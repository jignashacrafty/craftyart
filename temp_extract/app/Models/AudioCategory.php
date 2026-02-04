<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AudioCategory
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AudioItem> $audioItem
 * @property-read int|null $audio_item_count
 * @property-read AudioCategory|null $parentCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AudioCategory> $subcategories
 * @property-read int|null $subcategories_count
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AudioCategory extends Model
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

    public function audioItem()
    {
        return $this->hasMany(AudioItem::class);
    }
    public function subcategories()
    {
        return $this->hasMany(AudioCategory::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(AudioCategory::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = AudioCategory::where('parent_category_id', 0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = AudioCategory::where('id', $category)->get();
        if (empty($categories->toArray())) {
            $categories = AudioCategory::where('id_name', $category)->get();
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