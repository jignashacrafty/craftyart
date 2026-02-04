<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\LockType
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @method static \Illuminate\Database\Eloquent\Builder|LockType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LockType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LockType query()
 * @method static \Illuminate\Database\Eloquent\Builder|LockType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LockType whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LockType whereValue($value)
 * @mixin \Eloquent
 */
class LockType extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
