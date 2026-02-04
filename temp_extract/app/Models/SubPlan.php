<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\SubPlan
 *
 * @property int $id
 * @property string|null $string_id
 * @property string|null $plan_id
 * @property int $duration_id
 * @property array $plan_details
 * @property int|null $deleted
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\PlanDuration|null $category
 * @property-read \App\Models\PlanDuration|null $duration
 * @property-read \App\Models\Plan|null $plan
 * @property-read \App\Models\Plan|null $planRef
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereDurationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan wherePlanDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan wherePlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereStringId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SubPlan whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SubPlan extends Model
{
    protected $connection = 'mysql';

    // Add the fields that can be mass-assigned
    protected $fillable = [
        "string_id",
        'plan_id',
        'deleted',
        'duration_id',
        'plan_details',
    ];

    // SubPlan.php
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function planRef(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function duration(): BelongsTo
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id');
    }

    public function getPlanDetailsAttribute($value): array
    {
        return $value === null ? [] : json_decode($value, true);
    }

}
