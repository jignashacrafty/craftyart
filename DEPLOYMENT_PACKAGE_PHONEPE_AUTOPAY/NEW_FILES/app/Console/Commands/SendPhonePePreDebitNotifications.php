<?php

namespace App\Console\Commands;

use App\Models\PhonePeSubscription;
use App\Jobs\ProcessPhonePePreDebitNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPhonePePreDebitNotifications extends Command
{
    protected $signature = 'phonepe:send-predebit-notifications';
    protected $description = 'Send pre-debit notifications 24 hours before billing';

    public function handle()
    {
        $this->info('🔄 Starting pre-debit notification process...');

        // Get subscriptions due for billing tomorrow
        $subscriptions = PhonePeSubscription::getDueForPreDebitNotification();

        $this->info("📊 Found {$subscriptions->count()} subscriptions due for notification");

        foreach ($subscriptions as $subscription) {
            try {
                $this->info("📤 Processing subscription: {$subscription->merchant_subscription_id}");

                // Dispatch job
                ProcessPhonePePreDebitNotification::dispatch($subscription);

                $this->info("✅ Notification queued for subscription: {$subscription->merchant_subscription_id}");

            } catch (\Exception $e) {
                $this->error("❌ Failed for subscription {$subscription->merchant_subscription_id}: {$e->getMessage()}");
                Log::error('Pre-debit notification command error', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info('✅ Pre-debit notification process completed');
        return 0;
    }
}
