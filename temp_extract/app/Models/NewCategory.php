<?php

namespace App\Models;

use App\Http\Controllers\Api\HelperController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\NewCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string|null $cat_link
 * @property string|null $child_cat_ids
 * @property string|null $id_name
 * @property string|null $canonical_link
 * @property string|null $related_tags
 * @property string|null $string_id
 * @property int|null $emp_id
 * @property string|null $seo_emp_id
 * @property int|null $app_id
 * @property string|null $meta_title
 * @property string $primary_keyword
 * @property string|null $meta_desc
 * @property string $tag_line
 * @property string|null $h1_tag
 * @property string|null $h2_tag
 * @property string|null $short_desc
 * @property string|null $long_desc
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $category_name
 * @property string|null $size
 * @property string $category_thumb
 * @property string|null $banner
 * @property string|null $mockup
 * @property string|null $top_keywords
 * @property string $fldr_str
 * @property string|null $cta
 * @property int $imp
 * @property int $sequence_number
 * @property int $total_templates
 * @property int $status
 * @property int $no_index
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $child_updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\NewSearchTag> $newSearchTag
 * @property-read int|null $new_search_tag_count
 * @method static Builder|NewCategory newModelQuery()
 * @method static Builder|NewCategory newQuery()
 * @method static Builder|NewCategory query()
 * @method static Builder|NewCategory whereAppId($value)
 * @method static Builder|NewCategory whereBanner($value)
 * @method static Builder|NewCategory whereCanonicalLink($value)
 * @method static Builder|NewCategory whereCatLink($value)
 * @method static Builder|NewCategory whereCategoryName($value)
 * @method static Builder|NewCategory whereCategoryThumb($value)
 * @method static Builder|NewCategory whereChildCatIds($value)
 * @method static Builder|NewCategory whereChildUpdatedAt($value)
 * @method static Builder|NewCategory whereContents($value)
 * @method static Builder|NewCategory whereCreatedAt($value)
 * @method static Builder|NewCategory whereCta($value)
 * @method static Builder|NewCategory whereDeleted($value)
 * @method static Builder|NewCategory whereEmpId($value)
 * @method static Builder|NewCategory whereFaqs($value)
 * @method static Builder|NewCategory whereFldrStr($value)
 * @method static Builder|NewCategory whereH1Tag($value)
 * @method static Builder|NewCategory whereH2Tag($value)
 * @method static Builder|NewCategory whereId($value)
 * @method static Builder|NewCategory whereIdName($value)
 * @method static Builder|NewCategory whereImp($value)
 * @method static Builder|NewCategory whereLongDesc($value)
 * @method static Builder|NewCategory whereMetaDesc($value)
 * @method static Builder|NewCategory whereMetaTitle($value)
 * @method static Builder|NewCategory whereMockup($value)
 * @method static Builder|NewCategory whereNoIndex($value)
 * @method static Builder|NewCategory whereParentCategoryId($value)
 * @method static Builder|NewCategory wherePrimaryKeyword($value)
 * @method static Builder|NewCategory whereRelatedTags($value)
 * @method static Builder|NewCategory whereSeoEmpId($value)
 * @method static Builder|NewCategory whereSequenceNumber($value)
 * @method static Builder|NewCategory whereShortDesc($value)
 * @method static Builder|NewCategory whereSize($value)
 * @method static Builder|NewCategory whereStatus($value)
 * @method static Builder|NewCategory whereStringId($value)
 * @method static Builder|NewCategory whereTagLine($value)
 * @method static Builder|NewCategory whereTopKeywords($value)
 * @method static Builder|NewCategory whereTotalTemplates($value)
 * @method static Builder|NewCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class NewCategory extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'string_id',
        'canonical_link',
        'cat_link',
        'category_name',
        'primary_keyword',
        'id_name',
        'tag_line',
        'meta_title',
        'h1_tag',
        'h2_tag',
        'meta_desc',
        'short_desc',
        'long_desc',
        'category_thumb',
        'banner',
        'contents',
        'faqs',
        'mockup',
        'app_id',
        'top_keywords',
        'cta',
        'sequence_number',
        'status',
        'parent_category_id',
        'emp_id',
        'seo_emp_id',
        'fldr_str',
        'child_updated_at',
    ];

    public function checkIsLive()
    {
        return $this->status === 1;
    }


    public function parentCategory($isStatus = null)
    {
        if ($isStatus != null) {
            return $this->belongsTo(NewCategory::class, 'parent_category_id', 'id')->where('status', $isStatus);
        } else {
            return $this->belongsTo(NewCategory::class, 'parent_category_id', 'id');
        }
    }


    public function getRootParentId()
    {
        $category = $this;
        while ($category->parentCategory && $category->parentCategory->{"parent_category_id"} != 0) {
            $category = $category->parentCategory;
        }
        return $category->{"parent_category_id"};
    }

    public static function getAllCategoriesWithSubcategories($isStatus = null)
    {
        if ($isStatus != null) {
            $categories = NewCategory::where('parent_category_id', 0)->where('status', $isStatus)->get();
        } else {
            $categories = NewCategory::where('parent_category_id', 0)->get();
        }
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }
    public static function getCategoriesWithSubcategories($category, $isStatus = null)
    {
        if (is_numeric($category)) {
            if ($isStatus != null) {
                $categories = NewCategory::where('id', $category)->where('status', $isStatus)->get();
            } else {
                $categories = NewCategory::where('id', $category)->get();
            }
        } else {
            if ($isStatus != null) {
                $categories = NewCategory::where('id_name', $category)->where('status', $isStatus)->get();
            } else {
                $categories = NewCategory::where('id_name', $category)->get();
            }
        }

        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }



    public static function getCategoriesWithSubcategories2(array $childCatIds, $isStatus = null)
    {
        $subCategories = NewCategory::whereIn('id', $childCatIds)
            ->where('status', 1)
            ->get();

        $extractSubcategories = function ($subcategories) use (&$extractSubcategories) {
            return collect($subcategories)->map(function ($subcategory) use ($extractSubcategories) {
                return [
                    'id' => $subcategory['id'],
                    'category_name' => $subcategory['category_name'],
                    'category_thumb' => HelperController::$mediaUrl . $subcategory['category_thumb'],
                    'id_name' => $subcategory['id_name'],
                    'status' => $subcategory['status'],
                ];
            })->toArray();
        };
        return $extractSubcategories($subCategories);
    }

    protected function getSubcategoriesTree($isStatus = null)
    {
        $subcategories = $this->subcategories($isStatus)->get();
        foreach ($subcategories as $subcategory) {
            $subcategory->subcategories = $subcategory->getSubcategoriesTree($isStatus);
        }
        return $subcategories;
    }

        public function subcategories($isStatus = null)
        {
            if ($isStatus != null) {
                return $this->hasMany(NewCategory::class, 'parent_category_id', 'id')->where('status', $isStatus);
            } else {
                return $this->hasMany(NewCategory::class, 'parent_category_id', 'id');
            }
        }


    public function newSearchTag(): HasMany
    {
        return $this->hasMany(NewSearchTag::class, 'new_category_id', 'id');
    }

    public static function getAllIMPCategoriesWithSubcategories($isStatus = null)
    {
        if ($isStatus != null) {
            $categories = NewCategory::where('parent_category_id', 0)->where('imp', 1)->where('status', $isStatus)->get();
        } else {
            $categories = NewCategory::where('parent_category_id', 0)->where('imp', 1)->get();
        }
        foreach ($categories as $category) {
            $category->subcategories = $category->getSubcategoriesTree($isStatus);
        }
        return $categories;
    }

}