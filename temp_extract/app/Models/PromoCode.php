<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\PromoCode
 *
 * @property int $id
 * @property string|null $user_id
 * @property string $promo_code
 * @property int $disc
 * @property string|null $type
 * @property int $status
 * @property string|null $expiry_date
 * @property int|null $disc_upto_inr
 * @property int|null $min_cart_inr
 * @property int|null $disc_upto_usd
 * @property int|null $min_cart_usd
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read mixed $user_names
 * @method static Builder|PromoCode newModelQuery()
 * @method static Builder|PromoCode newQuery()
 * @method static Builder|PromoCode query()
 * @method static Builder|PromoCode whereCreatedAt($value)
 * @method static Builder|PromoCode whereDisc($value)
 * @method static Builder|PromoCode whereDiscUptoInr($value)
 * @method static Builder|PromoCode whereDiscUptoUsd($value)
 * @method static Builder|PromoCode whereExpiryDate($value)
 * @method static Builder|PromoCode whereId($value)
 * @method static Builder|PromoCode whereMinCartInr($value)
 * @method static Builder|PromoCode whereMinCartUsd($value)
 * @method static Builder|PromoCode wherePromoCode($value)
 * @method static Builder|PromoCode whereStatus($value)
 * @method static Builder|PromoCode whereType($value)
 * @method static Builder|PromoCode whereUpdatedAt($value)
 * @method static Builder|PromoCode whereUserId($value)
 * @mixin \Eloquent
 */
class PromoCode extends Model
{
    use HasFactory;
    protected $table = 'promo_codes';
    protected $connection = 'mysql';
    public const FILLABLE_FIELDS = [
        "user_id",
        'promo_code',
        'disc',
        'additional_days',
        'type',
        'status',
        'expiry_date',
        'disc_upto_inr',
        'min_cart_inr',
        'disc_upto_usd',
        'min_cart_usd',
    ];
    protected $fillable = self::FILLABLE_FIELDS;

    public function getUserNamesAttribute()
    {
        $ids = json_decode($this->user_id, true);
        if (empty($ids) || !is_array($ids)) {
            return 'Not Applicable';
        }
        $emails = UserData::whereIn('id', $ids)->pluck('email');
        return $emails->isNotEmpty() ? $emails->implode('<br>') : 'Not Applicable';
    }
}