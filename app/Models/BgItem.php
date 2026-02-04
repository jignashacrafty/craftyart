<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\BgItem
 *
 * @property int $id
 * @property int|null $emp_id
 * @property int $bg_cat_id
 * @property string $bg_name
 * @property string $bg_thumb
 * @property string $bg_image
 * @property int $width
 * @property int $height
 * @property int $bg_type
 * @property int $is_premium
 * @property int $status
 * @property int $latest
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BgCategory|null $BgCategory
 * @property-read \App\Models\BgMode|null $BgMode
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereBgCatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereBgImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereBgName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereBgThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereBgType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereLatest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgItem whereWidth($value)
 * @mixin \Eloquent
 */
class BgItem extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        "emp_id",
        "bg_cat_id",
        "bg_name",
        "bg_thumb",
        "bg_image",
        "width",
        "height",
        "bg_type",
        "is_premium",
        "status",
    ];

    public function BgCategory(): BelongsTo
    {
        return $this->belongsTo(BgCategory::class, 'bg_cat_id');
    }

    public function BgMode(): BelongsTo{
        return $this->belongsTo(BgMode::class, 'bg_type', 'value');
    }

}
