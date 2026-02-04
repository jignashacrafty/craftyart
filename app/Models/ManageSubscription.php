<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ManageSubscription
 *
 * @property int $id
 * @property int $user_id
 * @property int $is_base_price
 * @property string $package_name
 * @property string $desc
 * @property int $validity
 * @property float $actual_price
 * @property int|null $actual_price_dollar
 * @property float $price
 * @property int|null $price_dollar
 * @property int $months
 * @property int $has_offer
 * @property int $sequence_number
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription query()
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereActualPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereActualPriceDollar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereDesc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereHasOffer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereIsBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription wherePackageName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription wherePriceDollar($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereSequenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ManageSubscription whereValidity($value)
 * @mixin \Eloquent
 */
class ManageSubscription extends Model
{
    use HasFactory;

    protected $connection = 'mysql';

    protected $fillable = ['user_id','is_base_price','package_name','desc','validity','actual_price','actual_price_dollar','price','price_dollar','months','has_offer','sequence_number','status'];
}
