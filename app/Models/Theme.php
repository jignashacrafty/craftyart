<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Theme
 *
 * @property int $id
 * @property string|null $id_name
 * @property string|null $new_category_id
 * @property int|null $emp_id
 * @property string $name
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Theme newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Theme newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Theme query()
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereNewCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Theme whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Theme extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
