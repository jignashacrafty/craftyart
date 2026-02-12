<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhonePeAutoPayToken extends Model
{
    protected $table = 'phonepe_autopay_tokens';
    
    protected $fillable = [
        'user_id',
        'merchant_transaction_id',
        'auth_request_id',
        'status',
        'amount',
        'upi_id',
        'contact_no',
        'response_data'
    ];
    
    protected $casts = [
        'response_data' => 'array',
        'amount' => 'decimal:2'
    ];
    
    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id', 'id');
    }
    
    public function subscriptions()
    {
        return $this->hasMany(PhonePeSubscription::class, 'token_id');
    }
    
    /**
     * Check if token is active
     */
    public function isActive(): bool
    {
        return in_array(strtoupper($this->status), ['ACTIVE', 'AUTHORIZED']);
    }
}
