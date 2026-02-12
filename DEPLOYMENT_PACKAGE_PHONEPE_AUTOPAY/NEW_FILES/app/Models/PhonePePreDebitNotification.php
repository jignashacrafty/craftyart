<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePePreDebitNotification extends Model
{
    protected $table = 'phonepe_pre_debit_notifications';
    
    protected $fillable = [
        'subscription_id',
        'merchant_subscription_id',
        'notification_date',
        'debit_date',
        'amount',
        'phonepe_order_id',
        'phonepe_status',
        'phonepe_response',
        'whatsapp_status',
        'whatsapp_response',
        'user_id',
        'phone',
        'overall_status',
        'retry_count',
        'last_retry_at'
    ];
    
    protected $casts = [
        'notification_date' => 'date',
        'debit_date' => 'date',
        'phonepe_response' => 'array',
        'whatsapp_response' => 'array',
        'last_retry_at' => 'datetime',
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
     * Check if notification was sent successfully
     */
    public function isSent(): bool
    {
        return $this->overall_status === 'sent';
    }
    
    /**
     * Check if notification can be retried
     */
    public function canRetry(): bool
    {
        return $this->retry_count < 3 && $this->overall_status !== 'sent';
    }
    
    /**
     * Increment retry count
     */
    public function incrementRetry()
    {
        $this->retry_count++;
        $this->last_retry_at = now();
        $this->save();
    }
}
