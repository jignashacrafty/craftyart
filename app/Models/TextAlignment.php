<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\TextAlignment
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @property string|null $stringVal
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment query()
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment whereStringVal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TextAlignment whereValue($value)
 * @mixin \Eloquent
 */
class TextAlignment extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
