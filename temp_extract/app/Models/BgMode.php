<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\BgMode
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BgMode whereValue($value)
 * @mixin \Eloquent
 */
class BgMode extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
