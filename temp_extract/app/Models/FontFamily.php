<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\FontFamily
 *
 * @property int $id
 * @property int $emp_id
 * @property string|null $fontFamily
 * @property string|null $fontThumb
 * @property string|null $supportType
 * @property int $support_bold
 * @property int $support_italic
 * @property string|null $uniname
 * @property int $is_premium
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily query()
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereFontFamily($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereFontThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereSupportBold($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereSupportItalic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereSupportType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereUniname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FontFamily whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class FontFamily extends Model
{
	protected $table = 'font_families';
	protected $connection = 'mysql';
    use HasFactory;
}
