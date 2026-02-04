<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Style
 *
 * @property int $id
 * @property string|null $id_name
 * @property int $emp_id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Style newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Style newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Style query()
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Style whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Style extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
