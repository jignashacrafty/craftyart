<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\GifCategory
 *
 * @property int $id
 * @property int|null $parent_category_id
 * @property int $emp_id
 * @property string $name
 * @property string $thumb
 * @property string $sequence_number
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereParentCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GifCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class GifCategory extends Model
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
}