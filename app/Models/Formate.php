<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Formate
 *
 * @property int $id
 * @property string $name
 * @property int $status
 * @property int|null $emp_id
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Formate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Formate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereEmpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Formate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Formate extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $fillable = ['name'];
}
