<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\PaymentConfiguration
 *
 * @property int $id
 * @property string $payment_scope
 * @property string $gateway
 * @property array $credentials
 * @property int $is_active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @method static Builder|PaymentConfiguration newModelQuery()
 * @method static Builder|PaymentConfiguration newQuery()
 * @method static Builder|PaymentConfiguration query()
 * @method static Builder|PaymentConfiguration whereCreatedAt($value)
 * @method static Builder|PaymentConfiguration whereCredentials($value)
 * @method static Builder|PaymentConfiguration whereGateway($value)
 * @method static Builder|PaymentConfiguration whereId($value)
 * @method static Builder|PaymentConfiguration whereIsActive($value)
 * @method static Builder|PaymentConfiguration wherePaymentScope($value)
 * @method static Builder|PaymentConfiguration whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentConfiguration extends Model
{
    protected $table = 'payment_configurations';
    protected $connection = 'mysql';
    use HasFactory;

    protected $fillable = [
        'payment_scope',
        'gateway',
        'credentials',
        'payment_types',
        'is_active'
    ];

    protected $casts = [
        'credentials' => 'array',
        'payment_types' => 'array',
        'is_active' => 'boolean',
    ];
}
