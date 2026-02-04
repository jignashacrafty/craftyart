<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Subscription
 *
 * @property int $id
 * @property int $is_base_price
 * @property string $package_name
 * @property string $desc
 * @property int $validity
 * @property float $actual_price
 * @property float|null $actual_price_dollar
 * @property float $price
 * @property float|null $price_dollar
 * @property int $months
 * @property int $has_offer
 * @property int $sequence_number
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereActualPrice($value)
 * @method static Builder|Subscription whereActualPriceDollar($value)
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereDesc($value)
 * @method static Builder|Subscription whereHasOffer($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription whereIsBasePrice($value)
 * @method static Builder|Subscription whereMonths($value)
 * @method static Builder|Subscription wherePackageName($value)
 * @method static Builder|Subscription wherePrice($value)
 * @method static Builder|Subscription wherePriceDollar($value)
 * @method static Builder|Subscription whereSequenceNumber($value)
 * @method static Builder|Subscription whereStatus($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereValidity($value)
 * @mixin \Eloquent
 */
class Subscription extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
