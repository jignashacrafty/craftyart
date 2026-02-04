<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\OfferPackage
 *
 * @property int $id
 * @property string $string_id
 * @property string $plan_id
 * @property string $duration_id
 * @property string $bounce_code_id
 * @property string|null $sub_plan_id
 * @property int|null $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read \App\Models\BonusPackage|null $BonusPackage
 * @property-read \App\Models\PlanDuration|null $duration
 * @property-read \App\Models\Plan|null $plan
 * @property-read \App\Models\SubPlan|null $subPlan
 * @method static Builder|OfferPackage newModelQuery()
 * @method static Builder|OfferPackage newQuery()
 * @method static Builder|OfferPackage query()
 * @method static Builder|OfferPackage whereBounceCodeId($value)
 * @method static Builder|OfferPackage whereCreatedAt($value)
 * @method static Builder|OfferPackage whereDurationId($value)
 * @method static Builder|OfferPackage whereId($value)
 * @method static Builder|OfferPackage wherePlanId($value)
 * @method static Builder|OfferPackage whereStatus($value)
 * @method static Builder|OfferPackage whereStringId($value)
 * @method static Builder|OfferPackage whereSubPlanId($value)
 * @method static Builder|OfferPackage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OfferPackage extends Model
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'offer_package';

    protected $fillable = [
        'string_id',
        'plan_id',
        'duration_id',
        'sub_plan_id',
        'bounce_code_id',
        'status'
    ];

    // 🔹 Relationships
    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'string_id');
    }

    public function subPlan()
    {
        return $this->belongsTo(SubPlan::class, 'sub_plan_id', 'string_id');
    }

    public function duration()
    {
        return $this->belongsTo(PlanDuration::class, 'duration_id', 'id');
    }

    public function BonusPackage()
    {
        return $this->belongsTo(BonusPackage::class, 'bounce_code_id', 'id');
    }
}
