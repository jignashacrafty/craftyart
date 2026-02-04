<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * App\Models\Size
 *
 * @property int $id
 * @property string $size_name
 * @property string $thumb
 * @property string|null $new_category_id
 * @property string|null $id_name
 * @property int $width_ration
 * @property int $height_ration
 * @property int $width
 * @property int $height
 * @property string $paper_size
 * @property string|null $unit
 * @property int|null $status 1 : Active | 0:UnActive
 * @property int|null $emp_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @method static \Illuminate\Database\Eloquent\Builder|Size newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Size newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Size query()
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereHeightRation($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereNewCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size wherePaperSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereSizeName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Size whereWidthRation($value)
 * @mixin \Eloquent
 */
class Size extends Model
{
    use HasFactory, Notifiable;
    protected $connection = 'mysql';
    protected $fillable = ['size_name', 'thumb', 'id_name', 'width_ration', 'width', 'new_category_id', 'height_ration', 'height', 'paper_size', 'emp_id', 'status'];

    public static function getAllSizes()
    {
        $sizes = Size::all();
        return $sizes;
    }

}