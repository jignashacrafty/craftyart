<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanCategoryFeature
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlanFeature> $Planfeatures
 * @property-read int|null $planfeatures_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanCategoryFeature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanCategoryFeature extends Model
{
    use HasFactory;
    protected $connection = 'mysql';

    protected $fillable = ["name"];

    public function Planfeatures()
    {
        return $this->hasMany(PlanFeature::class, "category_feature_id", "id");
    }
}
