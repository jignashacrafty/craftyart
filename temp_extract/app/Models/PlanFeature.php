<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanFeature
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property int $category_feature_id
 * @property string|null $appearance_type
 * @property string|null $appearance_meta_data
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\PlanCategoryFeature|null $categoryFeatures
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Plan> $plans
 * @property-read int|null $plans_count
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereAppearanceMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereAppearanceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereCategoryFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanFeature whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PlanFeature extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        "name",
        "slug",
        "description",
        "category_feature_id",
        "appearance_type",
    ];
    public const ACTIVE = 1;
    public const INACTIVE = 0;
    public static $STATUS = [
        self::ACTIVE => "Active",
        self::INACTIVE => "InActive"
    ];

    public function categoryFeatures()
    {
        return $this->belongsTo(PlanCategoryFeature::class, "category_feature_id", "id");
    }
    public function plans()
    {
        return $this->belongsToMany(Plan::class, 'feature_plan', 'plan_feature_id', 'plan_id');
    }

}
