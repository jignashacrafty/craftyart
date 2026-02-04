<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PromoCodeTranscation
 *
 * @property int $id
 * @property string $user_id
 * @property string $promo_code
 * @property string $device_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation query()
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation whereDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation wherePromoCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PromoCodeTranscation whereUserId($value)
 * @mixin \Eloquent
 */
class PromoCodeTranscation extends Model
{
    protected $table = 'promo_code_transcation';
    protected $connection = 'mysql';
    use HasFactory;
}
