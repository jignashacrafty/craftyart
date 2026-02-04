<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Uploads
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
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads query()
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereAssetSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereAssetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereCompressVdo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereRatio($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereThumbnail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereTrashed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Uploads whereWidth($value)
 * @mixin \Eloquent
 */
class Uploads extends Model
{
    protected $table = 'uploads';
    protected $connection = 'mysql';
    use HasFactory;
}
