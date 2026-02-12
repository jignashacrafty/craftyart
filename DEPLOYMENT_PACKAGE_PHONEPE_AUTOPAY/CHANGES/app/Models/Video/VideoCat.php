<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Video\VideoCat
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property int $emp_id
 * @property string $category_name
 * @property string $category_thumb
 * @property int $sequence_number
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read VideoCat|null $parentCategory
 * @property-read \Illuminate\Database\Eloquent\Collection<int, VideoCat> $subcategories
 * @property-read int|null $subcategories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Video\VideoTemplate> $videoTemplates
 * @property-read int|null $video_templates_count
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereCategoryThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCat whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoCat extends Model
{
	protected $table = 'main_categories';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;

    protected $fillable = [
        'category_name',
        'id_name',
        'canonical_link',
        'seo_emp_id',
        'meta_title',
        'primary_keyword',
        'h1_tag',
        'tag_line',
        'meta_desc',
        'short_desc',
        'h2_tag',
        'long_desc',
        'category_thumb',
        'mockup',
        'banner',
        'app_id',
        'contents',
        'faqs',
        'top_keywords',
        'parent_category_id',
        'sequence_number',
        'status',
        'emp_id'
    ];

    protected $casts = [
        'contents' => 'array',
        'faqs' => 'array',
        'top_keywords' => 'array',
    ];


    public function videoTemplates()
    {
        return $this->hasMany(VideoTemplate::class,'category_id','id');
    }

    public function subcategories()
    {
        return $this->hasMany(VideoCat::class, 'parent_category_id', 'id');
    }

    public function parentCategory()
    {
        return $this->belongsTo(VideoCat::class, 'parent_category_id', 'id');
    }

    public static function getAllCategoriesWithSubcategories()
    {
        $categories = VideoCat::where('parent_category_id',0)->get();
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree();
        }
        return $categories;
    }


    public static function getCategoriesWithSubcategories($category)
    {
        $categories = VideoCat::where('id',$category)->get();

        if ( empty($categories->toArray()) ) {
            $categories = VideoCat::where('id_name',$category)->get();
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
