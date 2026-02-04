<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VideoItem
 *
 * @property int $id
 * @property int $video_category_id
 * @property int $emp_id
 * @property string $name
 * @property string $thumb
 * @property string $file
 * @property string $compress_vdo
 * @property string|null $duration
 * @property string|null $height
 * @property string|null $width
 * @property string $size
 * @property int $is_premium
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\VideoCategory|null $videoCategory
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereCompressVdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereVideoCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoItem whereWidth($value)
 * @mixin \Eloquent
 */
class VideoItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'video_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'compress_vdo',
        'thumbnail',
        'duration',
        'height',
        'width',
        'size',
        'is_premium',
        'status',
    ];


    public function videoCategory()
    {
        return $this->belongsTo(VideoCategory::class, 'video_category_id');
    }


}