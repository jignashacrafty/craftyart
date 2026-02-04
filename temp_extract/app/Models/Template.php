<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Template
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $app_id
 * @property string|null $category_id
 * @property string|null $sub_cat_id
 * @property string|null $style_id
 * @property string|null $theme_id
 * @property string|null $interest_id
 * @property string|null $lang_id
 * @property int $bg_cat_id
 * @property int $bg_id
 * @property string $post_name
 * @property string $post_thumb
 * @property int $back_image_type
 * @property string|null $back_image
 * @property string|null $back_color
 * @property int $grad_angle
 * @property int $grad_ratio
 * @property string $ratio
 * @property int $width
 * @property int $height
 * @property string $component_info
 * @property string $text_info
 * @property string|null $description
 * @property string|null $keywords
 * @property string|null $start_date
 * @property string|null $end_date
 * @property int|null $size
 * @property int|null $trending_views
 * @property int|null $views
 * @property int $is_premium
 * @property int $status
 * @property int $latest
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Template newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Template query()
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereBackColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereBackImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereBackImageType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereBgCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereBgId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereComponentInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereGradAngle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereGradRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereInterestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereLangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereLatest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template wherePostName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template wherePostThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereStyleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereSubCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereTextInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereThemeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereTrendingViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Template whereWidth($value)
 * @mixin \Eloquent
 */
class Template extends Model
{
    protected $connection = 'mysql';
    use HasFactory;


}