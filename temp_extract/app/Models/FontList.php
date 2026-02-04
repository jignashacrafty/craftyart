<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FontList
 *
 * @property int $id
 * @property int $emp_id
 * @property int|null $fontFamilyId
 * @property string|null $fontName
 * @property string|null $fontType
 * @property string|null $fontUrl
 * @property int $fontWeight
 * @property int $support_bold
 * @property int $support_italic
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FontList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FontList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FontList query()
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereFontFamilyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereFontName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereFontType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereFontUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereFontWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereSupportBold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereSupportItalic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontList whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FontList extends Model
{
	protected $table = 'font_list';
	protected $connection = 'mysql';
    use HasFactory;
}
