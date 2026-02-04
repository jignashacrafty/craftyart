<?php

namespace App\Models\Video;

use App\Models\UserData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Video\VideoPurchaseHistory
 *
 * @property int $id
 * @property string $user_id
 * @property string $product_id
 * @property int $product_type
 * @property string $transaction_id
 * @property string $payment_id
 * @property string $currency_code
 * @property string $amount
 * @property string $payment_method
 * @property string $from_where
 * @property int|null $isManual
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read UserData|null $userData
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereCurrencyCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereFromWhere($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereIsManual($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereProductType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|VideoPurchaseHistory whereUserId($value)
 * @mixin \Eloquent
 */
class VideoPurchaseHistory extends Model
{
	protected $table = 'purchase_history';
	protected $connection = 'crafty_video_mysql';
    use HasFactory;

    public function userData()
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }

}
