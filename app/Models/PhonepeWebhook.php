<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PhonepeWebhook
 *
 * @property int $id
 * @property string $transaction_id
 * @property string $code
 * @property string $json
 * @method static Builder|PhonepeWebhook newModelQuery()
 * @method static Builder|PhonepeWebhook newQuery()
 * @method static Builder|PhonepeWebhook query()
 * @method static Builder|PhonepeWebhook whereCode($value)
 * @method static Builder|PhonepeWebhook whereId($value)
 * @method static Builder|PhonepeWebhook whereJson($value)
 * @method static Builder|PhonepeWebhook whereTransactionId($value)
 * @mixin \Eloquent
 */
class PhonepeWebhook extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'phonepe_webhook';

    protected $fillable = ["transaction_id","code","json"];

    
}