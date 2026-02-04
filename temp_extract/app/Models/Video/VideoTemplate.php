<?php

namespace App\Models\Video;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Video\VideoTemplate
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $relation_id
 * @property string $string_id
 * @property int $category_id
 * @property string|null $video_name
 * @property string $folder_name
 * @property string|null $video_thumb
 * @property string|null $video_url
 * @property string $video_zip_url
 * @property int $width
 * @property int $height
 * @property int $watermark_height
 * @property int $template_type
 * @property int|null $do_front_lottie
 * @property string|null $editable_image
 * @property string|null $editable_text
 * @property string|null $keyword
 * @property int|null $change_text
 * @property int $change_music
 * @property int $encrypted
 * @property string|null $encryption_key
 * @property int $is_premium
 * @property int $pages
 * @property int $status
 * @property int $isDeleted
 * @property int $views
 * @property int $daily_views
 * @property int $weekly_views
 * @property int|null $creation
 * @property int|null $daily_creation
 * @property int $weekly_creation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Video\VideoCat|null $videoCat
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereChangeMusic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereChangeText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereDailyCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereDailyViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereDoFrontLottie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereEditableImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereEditableText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereEncrypted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereEncryptionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereFolderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereKeyword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate wherePages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereRelationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereTemplateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereVideoName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereVideoThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereVideoZipUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereWatermarkHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereWeeklyCreation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereWeeklyViews($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoTemplate whereWidth($value)
 * @mixin \Eloquent
 */
class VideoTemplate extends Model
{
	protected $table = 'items';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;

    public function videoCat()
    {
        return $this->belongsTo(VideoCat::class,'category_id','id');
    }
}
