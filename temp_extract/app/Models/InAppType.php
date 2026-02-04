<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\InAppType
 *
 * @property int $id
 * @property int $type
 * @property string $value
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType query()
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|InAppType whereValue($value)
 * @mixin \Eloquent
 */
class InAppType extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
