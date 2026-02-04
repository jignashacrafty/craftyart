<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Religion
 *
 * @property int $id
 * @property string $religion_name
 * @property string|null $id_name
 * @property int|null $status
 * @property int|null $emp_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Religion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Religion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Religion query()
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereIdName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereReligionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Religion whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Religion extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = [
        'religion_name',
        'id_name',
        'emp_id',
        'status'
    ];
}
