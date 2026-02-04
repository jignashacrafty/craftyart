<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\VectorItem
 *
 * @property int $id
 * @property int $svg_category_id
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
 * @property-read \App\Models\VectorCategory|null $svgCategory
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereSvgCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VectorItem whereWidth($value)
 * @mixin \Eloquent
 */
class VectorItem extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        'svg_category_id',
        'emp_id',
        'name',
        'thumb',
        'file',
        'width',
        'height',
        'is_premium',
        'status',
    ];


    public function svgCategory(): BelongsTo
    {
        return $this->belongsTo(VectorCategory::class,'svg_category_id');
    }




}