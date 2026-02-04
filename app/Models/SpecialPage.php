<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\SpecialPage
 *
 * @property int $id
 * @property int|null $emp_id
 * @property string|null $string_id
 * @property int $cat_id
 * @property string $page_slug
 * @property string|null $canonical_link
 * @property string $meta_title
 * @property string $title
 * @property string $primary_keyword
 * @property string|null $related_tags
 * @property string $meta_desc
 * @property string $description
 * @property string|null $pre_breadcrumb
 * @property string $breadcrumb
 * @property string|null $banner_type
 * @property string|null $banner
 * @property string|null $hero_bg_option
 * @property string $colors
 * @property string|null $hero_background_image
 * @property string|null $body_background_image
 * @property string|null $button
 * @property string|null $button_link
 * @property string|null $contents
 * @property string|null $faqs
 * @property string $page_type
 * @property int|null $button_target
 * @property int|null $button_rel
 * @property string|null $resume_guide_content
 * @property string|null $resume_content
 * @property string|null $fldr_str
 * @property string|null $top_keywords
 * @property string|null $cta
 * @property int $updated_record
 * @property int $status
 * @property int $no_index
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NewCategory|null $newCategory
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage query()
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereBannerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereBodyBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereBreadcrumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereButton($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereButtonLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereButtonRel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereButtonTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereCanonicalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereColors($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereContents($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereCta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereFaqs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereFldrStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereHeroBackgroundImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereHeroBgOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereMetaDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereNoIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage wherePageSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage wherePageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage wherePreBreadcrumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage wherePrimaryKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereRelatedTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereResumeContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereResumeGuideContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereTopKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SpecialPage whereUpdatedRecord($value)
 * @mixin \Eloquent
 */
class SpecialPage extends Model
{
    use HasFactory;

    public const DRAFT = 0;
    public const PUBLISH = 1;

    public static $status = [
        "draft" => self::DRAFT,
        "publish" => self::PUBLISH
    ];

    public static $pageType = ["special", "tool", "kpage"];

    protected $table = 'special_pages';
    protected $fillable = ['string_id', 'cat_id', 'page_slug', 'meta_title', 'title', 'primary_keyword', "new_related_tags", 'meta_desc', 'description', 'pre_breadcrumb', 'breadcrumb', 'image', 'button_target', 'video', 'banner', 'banner_type', 'colors', 'button', 'button_link', 'contents', 'faqs', 'status', 'page_type', 'resume_guide_content', 'resume_content', 'hero_bg_option', 'hero_background_image', 'body_background_image', 'fldr_str', 'top_keywords', 'cta', 'canonical_link'];

    public function newCategory()
    {
        return $this->belongsTo(NewCategory::class, 'cat_id');
    }
}