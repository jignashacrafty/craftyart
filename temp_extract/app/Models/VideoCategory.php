<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\VideoCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property int $emp_id
 * @property string|null $name
 * @property string $thumb
 * @property string $sequence_number
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\VideoItem> $videoItem
 * @property-read int|null $video_item_count
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class VideoCategory extends Model
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

    public function videoItem()
    {
        return $this->hasMany(VideoItem::class);
    }
}