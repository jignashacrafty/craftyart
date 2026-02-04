<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\RawDatas
 *
 * @property int $id
 * @property string $string_id
 * @property string $user_id
 * @property int $asset_type
 * @property string $name
 * @property string|null $ratio
 * @property int $width
 * @property int $height
 * @property string $image
 * @property string|null $thumbnail
 * @property string|null $compress_vdo
 * @property string|null $duration
 * @property int $asset_size
 * @property int $trashed
 * @property int $deleted
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $status
 * @property-read mixed $category_name
 * @property-read mixed $raw_type
 * @property-read \Illuminate\Database\Eloquent\Collection<int, RawDatas> $rawItem
 * @property-read int|null $raw_item_count
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas query()
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereAssetSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereCompressVdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereTrashed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RawDatas whereWidth($value)
 * @mixin \Eloquent
 */
class RawDatas extends Model
{
    protected $table = 'raw_datas';
    protected $connection = 'mysql';
    use HasFactory;

    const ASSET_TYPES = [
        0 => 'image',
        1 => 'gif',
        2 => 'vector',
        3 => 'video',
        4 => 'audio',
        5 => 'frame',
    ];

    const RAW_TYPES = [
        0 => 'sticker',
        1 => 'background',
        2 => 'frame',
        3 => 'vector',
        4 => 'audio',
        5 => 'gif',
        6 => 'video',
    ];

    protected $fillable = [
        'id',
        'string_id',
        'user_id',
        'asset_type',
        'name',
        'ratio',
        'width',
        'height',
        'image',
        'thumbnail',
        'compress_vdo',
        'duration',
        'asset_size',
    ];

    public function getAssetTypeAttribute($value)
    {
        return self::ASSET_TYPES[$value] ?? 'Unknown';
    }

    public function rawItem()
    {
        return $this->hasMany(RawDatas::class);
    }

    public function getCategoryNameAttribute()
    {
        return self::RAW_TYPES[$this->category_id] ?? 'Not Assign';
    }

    public function getRawTypeAttribute($value)
    {
        return self::RAW_TYPES[$value] ?? 'Not Assign';
    }


}