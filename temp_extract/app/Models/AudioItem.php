<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AudioItem
 *
 * @property int $id
 * @property int $audio_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property string $thumb
 * @property string $file
 * @property int $duration
 * @property int $size
 * @property int $is_premium
 * @property int|null $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\AudioCategory|null $audioCategory
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereAudioCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AudioItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AudioItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'audio_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'duration',
        'size',
        'is_premium',
        'status',
    ];


    public function audioCategory()
    {
        return $this->belongsTo(AudioCategory::class);
    }
}
