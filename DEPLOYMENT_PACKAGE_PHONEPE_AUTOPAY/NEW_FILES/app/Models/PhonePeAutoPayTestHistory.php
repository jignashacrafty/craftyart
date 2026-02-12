<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeAutoPayTestHistory extends Model
{
    protected $table = 'phonepe_autopay_test_history';
    
    protected $fillable = [
        'merchant_order_id',
        'merchant_subscription_id',
        'phonepe_order_id',
        'upi_id',
        'mobile',
        'amount',
        'status',
        'subscription_state',
        'is_autopay_active',
        'autopay_count',
        'last_autopay_at',
        'next_autopay_at',
        'predebit_sent',
        'predebit_sent_at',
        'request_payload',
        'response_data',
        'notes'
    ];
    
    protected $casts = [
        'amount' => 'decimal:2',
        'is_autopay_active' => 'boolean',
        'predebit_sent' => 'boolean',
        'autopay_count' => 'integer',
        'last_autopay_at' => 'datetime',
        'next_autopay_at' => 'datetime',
        'predebit_sent_at' => 'datetime',
        'request_payload' => 'array',
        'response_data' => 'array',
    ];
}
