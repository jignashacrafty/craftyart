<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StickerCategory
 *
 * @property int $id
 * @property int|null $emp_id
 * @property string|null $stk_category_name
 * @property string|null $stk_category_thumb
 * @property int $sequence_number
 * @property int $status
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereStkCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereStkCategoryThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StickerCategory extends Model
{
	protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        'stk_category_name',
        'stk_category_thumb',
        'sequence_number',
        'status',
        'emp_id',
    ];
}
