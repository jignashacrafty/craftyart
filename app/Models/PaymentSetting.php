<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PaymentSetting
 *
 * @property int $id
 * @property string $razorpay_ki
 * @property string $razorpay_ck
 * @property string|null $stripe_sk
 * @property string|null $stripe_pk
 * @property string|null $stripe_ver
 * @property string|null $paypal_ci
 * @property string|null $paypal_sk
 * @property int|null $razorpay_status
 * @property int|null $stripe_status
 * @property int|null $paypal_status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting wherePaypalCi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting wherePaypalSk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting wherePaypalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereRazorpayCk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereRazorpayKi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereRazorpayStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereStripePk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereStripeSk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereStripeStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereStripeVer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentSetting extends Model
{
	protected $connection = 'mysql';
    use HasFactory;
}
