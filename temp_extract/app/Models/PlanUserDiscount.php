<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PlanUserDiscount
 *
 * @property int $id
 * @property int $discount_percentage
 * @property float $x
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount query()
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount whereDiscountPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PlanUserDiscount whereX($value)
 * @mixin \Eloquent
 */
class PlanUserDiscount extends Model
{
    use HasFactory;
    protected $table = 'plan_user_discount';

    protected $connection = 'mysql';
    protected $fillable = [
        "discount_percentage",
        "x",
    ];
}