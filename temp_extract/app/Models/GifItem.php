<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GifItem
 *
 * @property int $id
 * @property int $gif_category_id
 * @property int $emp_id
 * @property string $name
 * @property string $thumb
 * @property string $file
 * @property string $width
 * @property string $height
 * @property string $is_premium
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\GifCategory|null $gifCategory
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereGifCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifItem whereWidth($value)
 * @mixin \Eloquent
 */
class GifItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'gif_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'width',
        'height',
        'is_premium',
        'status',
    ];


    public function gifCategory()
    {
        return $this->belongsTo(GifCategory::class);
    }
}