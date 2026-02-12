<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PhonePeSubscription extends Model
{
    protected $table = 'phonepe_subscriptions';
    
    protected $fillable = [
        'user_id',
        'order_id',
        'plan_id',
        'merchant_subscription_id',
        'phonepe_subscription_id',
        'merchant_order_id',
        'amount',
        'currency',
        'frequency',
        'max_amount',
        'start_date',
        'next_billing_date',
        'end_date',
        'last_payment_date',
        'status',
        'subscription_status',
        'total_payments',
        'failed_payments',
        'last_payment_status',
        'error_code',
        'error_message',
        'failure_reason',
        'metadata'
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'next_billing_date' => 'date',
        'end_date' => 'date',
        'last_payment_date' => 'date',
        'metadata' => 'array',
        'amount' => 'decimal:2',
        'max_amount' => 'decimal:2'
    ];
    
    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(UserData::class, 'user_id', 'id');
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function plan()
    {
        return $this->belongsTo(Subscription::class, 'plan_id');
    }
    
    public function token()
    {
        return $this->belongsTo(PhonePeAutoPayToken::class, 'token_id');
    }
    
    public function transactions()
    {
        return $this->hasMany(PhonePeAutoPayTransaction::class, 'subscription_id');
    }
    
    public function preDebitNotifications()
    {
        return $this->hasMany(PhonePePreDebitNotification::class, 'subscription_id');
    }
    
    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === 'ACTIVE';
    }
    
    /**
     * Check if billing is due today
     */
    public function isDueToday(): bool
    {
        return $this->next_billing_date->isToday();
    }
    
    /**
     * Check if billing is due tomorrow
     */
    public function isDueTomorrow(): bool
    {
        return $this->next_billing_date->isTomorrow();
    }
    
    /**
     * Update next billing date
     */
    public function updateNextBillingDate()
    {
        $this->next_billing_date = match($this->frequency) {
            'Daily' => now()->addDay(),
            'Weekly' => now()->addWeek(),
            'Monthly' => now()->addMonth(),
            'Yearly' => now()->addYear(),
            default => now()->addMonth()
        };
        $this->save();
    }
    
    /**
     * Record successful payment
     */
    public function recordSuccessfulPayment()
    {
        $this->last_payment_date = now();
        $this->total_payments++;
        $this->failed_payments = 0; // Reset failed count
        $this->last_payment_status = 'COMPLETED';
        $this->status = 'ACTIVE';
        $this->updateNextBillingDate();
        $this->save();
    }
    
    /**
     * Record failed payment
     */
    public function recordFailedPayment($errorCode = null, $errorMessage = null)
    {
        $this->failed_payments++;
        $this->last_payment_status = 'FAILED';
        $this->error_code = $errorCode;
        $this->error_message = $errorMessage;
        
        // Cancel subscription after 3 failed attempts
        if ($this->failed_payments >= 3) {
            $this->status = 'CANCELLED';
            $this->failure_reason = 'Cancelled due to multiple payment failures';
        } else {
            $this->status = 'PAYMENT_FAILED';
        }
        
        $this->save();
    }
    
    /**
     * Get subscriptions due for billing today
     */
    public static function getDueForBillingToday()
    {
        return self::where('status', 'ACTIVE')
                   ->whereDate('next_billing_date', now()->toDateString())
                   ->get();
    }
    
    /**
     * Get subscriptions due for pre-debit notification
     */
    public static function getDueForPreDebitNotification()
    {
        return self::where('status', 'ACTIVE')
                   ->whereDate('next_billing_date', now()->addDay()->toDateString())
                   ->get();
    }
}
