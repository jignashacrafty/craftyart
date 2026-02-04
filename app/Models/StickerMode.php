<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StickerMode
 *
 * @property int $id
 * @property string $type
 * @property int $value
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|StickerMode whereValue($value)
 * @mixin \Eloquent
 */
class StickerMode extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
