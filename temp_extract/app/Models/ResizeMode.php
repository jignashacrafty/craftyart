<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ResizeMode
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ResizeMode whereValue($value)
 * @mixin \Eloquent
 */
class ResizeMode extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
