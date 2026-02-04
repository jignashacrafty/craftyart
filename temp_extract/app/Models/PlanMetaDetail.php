<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanMetaDetail
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PlanMetaDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanMetaDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanMetaDetail query()
 * @mixin \Eloquent
 */
class PlanMetaDetail extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = [
        "plan_id",
        "feature_id",
        "meta_feature_key",
        "meta_feature_value"
    ];
}
