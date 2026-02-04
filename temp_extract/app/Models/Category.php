<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $id_name
 * @property string $string_id
 * @property int|null $emp_id
 * @property int $assign_seo_id
 * @property int $app_id
 * @property string|null $meta_title
 * @property string $primary_keyword
 * @property string|null $meta_desc
 * @property string $tag_line
 * @property string|null $related_tags
 * @property string|null $h1_tag
 * @property string|null $h2_tag
 * @property string|null $short_desc
 * @property string|null $long_desc
 * @property string|null $canonical_link
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $category_name
 * @property string|null $size
 * @property string $category_thumb
 * @property string $banner
 * @property string|null $top_keywords
 * @property string $fldr_str
 * @property string|null $cta
 * @property int $imp
 * @property int $sequence_number
 * @property int $status
 * @property int $no_index
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedSeo
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereAssignSeoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCanonicalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCategoryThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereFaqs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereFldrStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereH1Tag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereH2Tag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereImp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereLongDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereMetaDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereNoIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePrimaryKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereRelatedTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereShortDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereTagLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereTopKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
    public function assignedSeo()
{
    return $this->belongsTo(User::class, 'seo_emp_id');
}

}
