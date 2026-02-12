<?php

namespace App\Console\Commands;

use App\Models\PhonePeSubscription;
use App\Jobs\ProcessPhonePeRecurringPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessPhonePeRecurringPayments extends Command
{
    protected $signature = 'phonepe:process-recurring-payments';
    protected $description = 'Process recurring payments for subscriptions due today';

    public function handle()
    {
        $this->info('🔄 Starting recurring payment process...');

        // Get subscriptions due for billing today
        $subscriptions = PhonePeSubscription::getDueForBillingToday();

        $this->info("📊 Found {$subscriptions->count()} subscriptions due for billing");

        foreach ($subscriptions as $subscription) {
            try {
                $this->info("💳 Processing subscription: {$subscription->merchant_subscription_id}");

                // Dispatch job
                ProcessPhonePeRecurringPayment::dispatch($subscription);

                $this->info("✅ Payment queued for subscription: {$subscription->merchant_subscription_id}");

            } catch (\Exception $e) {
                $this->error("❌ Failed for subscription {$subscription->merchant_subscription_id}: {$e->getMessage()}");
                Log::error('Recurring payment command error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('✅ Recurring payment process completed');
        return 0;
    }
}
