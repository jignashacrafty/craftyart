<?php

namespace App\Jobs;

use App\Models\PhonePeSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPhonePeRecurringPayment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $subscription;

    public function __construct(PhonePeSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function handle()
    {
        try {
            Log::info('💳 Processing recurring payment', [
                'subscription_id' => $this->subscription->id,
                'merchant_subscription_id' => $this->subscription->merchant_subscription_id,
                'next_billing_date' => $this->subscription->next_billing_date
            ]);

            // PhonePe will automatically debit via AutoPay
            // This job is just for monitoring and fallback

            // Wait for webhook callback
            // If webhook doesn't arrive in 1 hour, trigger manual redemption

        } catch (\Exception $e) {
            Log::error('❌ Recurring payment job failed', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
