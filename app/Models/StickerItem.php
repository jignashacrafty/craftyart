<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StickerItem
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $stk_cat_id
 * @property string $sticker_name
 * @property string $sticker_thumb
 * @property string $sticker_image
 * @property int $sticker_type
 * @property int $width
 * @property int $height
 * @property int $is_premium
 * @property int $status
 * @property int|null $latest
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereLatest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStickerImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStickerName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStickerThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStickerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereStkCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerItem whereWidth($value)
 * @mixin \Eloquent
 */
class StickerItem extends Model
{
    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'stk_cat_id',
        'emp_id',
        'sticker_thumb',
        'sticker_image',
        'sticker_type',
        'sticker_name',
        'width',
        'height',
        'is_premium',
        'status',
    ];
}