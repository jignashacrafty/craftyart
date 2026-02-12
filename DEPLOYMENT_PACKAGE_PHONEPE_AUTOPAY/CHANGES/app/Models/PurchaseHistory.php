<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PurchaseHistory
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
 * @property int $isManual
 * @property int $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \App\Models\UserData|null $userData
 * @method static Builder|PurchaseHistory newModelQuery()
 * @method static Builder|PurchaseHistory newQuery()
 * @method static Builder|PurchaseHistory query()
 * @method static Builder|PurchaseHistory whereAmount($value)
 * @method static Builder|PurchaseHistory whereCreatedAt($value)
 * @method static Builder|PurchaseHistory whereCurrencyCode($value)
 * @method static Builder|PurchaseHistory whereFromWhere($value)
 * @method static Builder|PurchaseHistory whereId($value)
 * @method static Builder|PurchaseHistory whereIsManual($value)
 * @method static Builder|PurchaseHistory wherePaymentId($value)
 * @method static Builder|PurchaseHistory wherePaymentMethod($value)
 * @method static Builder|PurchaseHistory whereProductId($value)
 * @method static Builder|PurchaseHistory whereProductType($value)
 * @method static Builder|PurchaseHistory whereStatus($value)
 * @method static Builder|PurchaseHistory whereTransactionId($value)
 * @method static Builder|PurchaseHistory whereUpdatedAt($value)
 * @method static Builder|PurchaseHistory whereUserId($value)
 * @mixin \Eloquent
 */
class PurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'product_type',
        'subscription_id',
        'transaction_id',
        'payment_id',
        'currency_code',
        'amount',
        'payment_method',
        'from_where',
        'isManual',
        'status',
        'contact_no',
        'payment_status',
        'phonepe_merchant_order_id',
        'phonepe_subscription_id',
        'phonepe_order_id',
        'phonepe_transaction_id',
        'is_autopay_enabled',
        'autopay_status',
        'autopay_activated_at',
        'next_autopay_date',
        'autopay_count'
    ];

    public function userData()
    {
        return $this->belongsTo(UserData::class,'user_id','uid');
    }
}
