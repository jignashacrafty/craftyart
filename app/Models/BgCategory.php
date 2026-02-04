<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BgCategory
 *
 * @property int $id
 * @property int|null $emp_id
 * @property string|null $bg_category_name
 * @property string|null $bg_category_thumb
 * @property int $sequence_number
 * @property int $status
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereBgCategoryName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereBgCategoryThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgCategory whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class BgCategory extends Model
{
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        "emp_id",
        "bg_category_name",
        "bg_category_thumb",
        "sequence_number",
        "status",
        "deleted",
    ];
}
