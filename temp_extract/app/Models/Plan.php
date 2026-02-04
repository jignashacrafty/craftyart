<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Plan
 *
 * @property int $id
 * @property string $name e.g., Basic, Standard, Premium
 * @property string|null $sub_title
 * @property string|null $btn_name
 * @property int $is_recommended
 * @property string $string_id
 * @property int|null $sequence_number
 * @property string|null $icon
 * @property string|null $description
 * @property array $appearance
 * @property int $is_free_type
 * @property int $status 1:Active | 0:InActive
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PlanFeature> $features
 * @property-read int|null $features_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SubPlan> $subPlans
 * @property-read int|null $sub_plans_count
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan query()
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereAppearance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereBtnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereIsFreeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereIsRecommended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereSubTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Plan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Plan extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        "id",
        "name",
        "btn_name",
        "sub_title",
        "is_recommended",
        "string_id",
        "sequence_number",
        "icon",
        "description",
        "appearance",
        "is_free_type",
        "status",
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(PlanFeature::class, 'feature_plan', 'string_id', 'plan_feature_id');
    }


    public function subPlans(): HasMany
    {
        return $this->hasMany(SubPlan::class, 'plan_id', 'id');
    }

    public function getAppearanceAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

}
