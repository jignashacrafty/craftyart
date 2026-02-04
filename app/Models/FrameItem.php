<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\FrameItem
 *
 * @property int $id
 * @property int $frame_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property string $thumb
 * @property string $file
 * @property int $type
 * @property int $width
 * @property int $height
 * @property int $is_premium
 * @property int|null $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\FrameCategory|null $frameCategory
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereFrameCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FrameItem whereWidth($value)
 * @mixin \Eloquent
 */
class FrameItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'frame_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'width',
        'height',
        'is_premium',
        'status',
    ];


    // public function frameCategory()
    // {
    //     return $this->belongsTo(FrameCategory::class);
    // }

    public function frameCategory(): BelongsTo
    {
        return $this->belongsTo(FrameCategory::class, 'frame_category_id');
    }

}
