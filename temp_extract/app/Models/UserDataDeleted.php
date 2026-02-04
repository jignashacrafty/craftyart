<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\UserDataDeleted
 *
 * @property int $id
 * @property int $user_int_id
 * @property string $uid
 * @property string $refer_id
 * @property string|null $stripe_cus_id
 * @property string|null $razorpay_cus_id
 * @property string|null $photo_uri
 * @property string $name
 * @property string|null $country_code
 * @property string|null $number
 * @property string|null $email
 * @property string $login_type
 * @property int $total_validity
 * @property int $validity
 * @property int|null $is_premium
 * @property int|null $special_user
 * @property int $can_update
 * @property string|null $utm_source
 * @property string|null $utm_medium
 * @property int|null $coins
 * @property string|null $device_id
 * @property string|null $fldr_str
 * @property string $creation_date
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereCanUpdate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereCoins($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereCreationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereFldrStr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereIsPremium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereLoginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted wherePhotoUri($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereRazorpayCusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereReferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereSpecialUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereStripeCusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereTotalValidity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereUserIntId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereUtmMedium($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereUtmSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDataDeleted whereValidity($value)
 * @mixin \Eloquent
 */
class UserDataDeleted extends Model
{
	protected $table = 'user_data_deleted';
	protected $connection = 'mysql';
    use HasFactory;
}
