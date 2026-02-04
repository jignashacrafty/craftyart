<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SpecialKeyword
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $cat_id
 * @property string|null $string_id
 * @property string $name
 * @property string $meta_title
 * @property string $title
 * @property string $primary_keyword
 * @property string|null $related_tags
 * @property string|null $h2_tag
 * @property string $meta_desc
 * @property string $short_desc
 * @property string|null $long_desc
 * @property string|null $banner
 * @property string|null $top_keywords
 * @property string $fldr_str
 * @property int $status
 * @property int $no_index
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property string|null $cta
 * @property string|null $canonical_link
 * @property string|null $contents
 * @property string|null $faqs
 * @property-read \App\Models\NewCategory|null $newCategory
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereCanonicalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereCta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereFaqs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereFldrStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereH2Tag($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereLongDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereMetaDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereNoIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword wherePrimaryKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereRelatedTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereShortDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereTopKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialKeyword whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SpecialKeyword extends Model
{
	protected $table = 'special_keywords';
	protected $connection = 'mysql';

	use HasFactory;

	public function newCategory()
	{
		return $this->belongsTo(NewCategory::class, 'cat_id');
	}
}
