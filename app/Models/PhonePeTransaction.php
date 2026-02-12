<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeTransaction extends Model
{
    protected $table = 'phonepe_transactions';
    
    protected $fillable = [
        'user_id',
        'merchant_order_id',
        'merchant_subscription_id',
        'phonepe_order_id',
        'phonepe_transaction_id',
        'transaction_type',
        'upi_id',
        'mobile',
        'amount',
        'status',
        'payment_state',
        'is_autopay_active',
        'autopay_count',
        'last_autopay_at',
        'next_autopay_at',
        'request_payload',
        'response_data',
        'notes'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'is_autopay_active' => 'boolean',
        'autopay_count' => 'integer',
        'last_autopay_at' => 'datetime',
        'next_autopay_at' => 'datetime',
        'request_payload' => 'array',
        'response_data' => 'array',
    ];
    
    /**
     * Get the user (UserData) for this transaction
     */
    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id', 'uid');
    }
    
    /**
     * Get notifications for this transaction
     */
    public function notifications()
    {
        return $this->hasMany(PhonePeNotification::class, 'merchant_order_id', 'merchant_order_id');
    }
    
    /**
     * Get the latest notification
     */
    public function latestNotification()
    {
        return $this->hasOne(PhonePeNotification::class, 'merchant_order_id', 'merchant_order_id')
            ->latest();
    }
}
