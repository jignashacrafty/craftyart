<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Interest
 *
 * @property int $id
 * @property string|null $id_name
 * @property string|null $new_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Interest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest query()
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereNewCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Interest whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Interest extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
