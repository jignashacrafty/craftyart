<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanDuration
 *
 * @property int $id
 * @property string $name
 * @property string|null $string_id
 * @property string $duration
 * @property int $is_annual
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereIsAnnual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanDuration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanDuration extends Model
{
  use HasFactory;

  protected $table = 'plan_duration';
  protected $connection = 'mysql';

  protected $fillable = [
    "name",
    "string_id",
    "duration",
    "is_annual",
  ];

}
