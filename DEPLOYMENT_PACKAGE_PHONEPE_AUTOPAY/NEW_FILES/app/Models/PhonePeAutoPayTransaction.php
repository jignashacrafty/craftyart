<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeAutoPayTransaction extends Model
{
    protected $table = 'phonepe_autopay_transactions';
    
    protected $fillable = [
        'subscription_id',
        'merchant_subscription_id',
        'merchant_order_id',
        'phonepe_transaction_id',
        'amount',
        'currency',
        'transaction_type',
        'status',
        'payment_status',
        'error_code',
        'error_message',
        'failure_reason',
        'webhook_data',
        'is_autopay'
    ];
    
    protected $casts = [
        'webhook_data' => 'array',
        'is_autopay' => 'boolean',
        'amount' => 'decimal:2'
    ];
    
    /**
     * Relationships
     */
    public function subscription()
    {
        return $this->belongsTo(PhonePeSubscription::class, 'subscription_id');
    }
    
    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }
    
    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
