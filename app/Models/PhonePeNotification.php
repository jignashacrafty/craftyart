<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeNotification extends Model
{
    protected $table = 'phonepe_notifications';
    
    protected $fillable = [
        'merchant_order_id',
        'merchant_subscription_id',
        'phonepe_order_id',
        'phonepe_transaction_id',
        'notification_type',
        'event_type',
        'amount',
        'status',
        'payment_method',
        'webhook_payload',
        'response_data',
        'is_processed',
        'processed_at',
        'notes'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'is_processed' => 'boolean',
        'processed_at' => 'datetime',
        'webhook_payload' => 'array',
        'response_data' => 'array',
    ];
    
    /**
     * Get the transaction for this notification
     */
    public function transaction()
    {
        return $this->belongsTo(PhonePeTransaction::class, 'merchant_order_id', 'merchant_order_id');
    }
}
