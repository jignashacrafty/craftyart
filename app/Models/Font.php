<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Font
 *
 * @property int $id
 * @property int|null $emp_id
 * @property string $name
 * @property string|null $fontFamily
 * @property string|null $postScriptName
 * @property int $fontWeight
 * @property string $extension
 * @property string|null $uniname
 * @property string $thumb
 * @property string $path
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Font newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Font newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Font query()
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereFontFamily($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereFontWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font wherePostScriptName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereThumb($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereUniname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Font whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Font extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
