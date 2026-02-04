<?php

namespace App\Models\Pricing;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Plan\PlanCategoryFeature
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read Collection<int, PlanFeature> $Planfeatures
 * @property-read int|null $planfeatures_count
 * @method static Builder|PlanCategoryFeature newModelQuery()
 * @method static Builder|PlanCategoryFeature newQuery()
 * @method static Builder|PlanCategoryFeature query()
 * @method static Builder|PlanCategoryFeature whereCreatedAt($value)
 * @method static Builder|PlanCategoryFeature whereId($value)
 * @method static Builder|PlanCategoryFeature whereName($value)
 * @method static Builder|PlanCategoryFeature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanCategoryFeature extends Model
{
    use HasFactory;
    protected $connection = 'crafty_pricing_mysql';

    protected $fillable = ["name"];

    public function Planfeatures()
    {
        return $this->hasMany(PlanFeature::class, "category_feature_id", "id");
    }
}
