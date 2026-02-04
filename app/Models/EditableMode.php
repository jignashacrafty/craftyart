<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EditableMode
 *
 * @property int $id
 * @property string $name
 * @property string|null $brand_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode query()
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EditableMode whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EditableMode extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
