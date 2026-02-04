<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
/**
 * App\Models\PaymentMetadata
 *
 * @property int $id
 * @property string $transaction_id
 * @property array $meta_data
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata query()
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereMetaData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|PaymentMetadata whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentMetadata extends Model
{
    protected $fillable = [
        'transaction_id',
        'meta_data',
        'status',
    ];
    protected $casts = [
        'meta_data' => 'array',
    ];
}