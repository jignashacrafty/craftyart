<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VirtualCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property string $id_name
 * @property string|null $string_id
 * @property int|null $emp_id
 * @property int $assign_seo_id
 * @property int|null $app_id
 * @property string|null $meta_title
 * @property string $primary_keyword
 * @property string|null $canonical_link
 * @property string|null $related_tags
 * @property string|null $meta_desc
 * @property string|null $h1_tag
 * @property string|null $h2_tag
 * @property string $tag_line
 * @property string|null $short_desc
 * @property string|null $long_desc
 * @property string $category_name
 * @property string|null $size
 * @property string $category_thumb
 * @property string|null $mockup
 * @property string|null $banner
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $fldr_str
 * @property string $virtual_query
 * @property string|null $top_keywords
 * @property string|null $cta
 * @property int $imp
 * @property int $sequence_number
 * @property int $status
 * @property int $no_index
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $assignedSeo
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereAssignSeoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereCanonicalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereCategoryThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereCta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereFaqs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereFldrStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereH1Tag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereH2Tag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereImp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereLongDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereMetaDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereMockup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereNoIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory wherePrimaryKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereRelatedTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereShortDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereTagLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereTopKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VirtualCategory whereVirtualQuery($value)
 * @mixin \Eloquent
 */
class VirtualCategory extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    public function assignedSeo()
{
    return $this->belongsTo(User::class, 'seo_emp_id');
}


}