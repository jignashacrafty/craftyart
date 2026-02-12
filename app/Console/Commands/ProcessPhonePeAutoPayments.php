<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PhonePeAutoPayService;
use App\Models\PurchaseHistory;
use Illuminate\Support\Facades\Log;

class ProcessPhonePeAutoPayments extends Command
{
    protected $signature = 'phonepe:process-autopay {--test : Run in test mode without actual charges}';
    protected $description = 'Process PhonePe AutoPay recurring payments for due subscriptions';

    public function handle()
    {
        $this->info('Starting PhonePe AutoPay processing...');
        
        $testMode = $this->option('test');
        
        if ($testMode) {
            $this->warn('Running in TEST MODE - No actual charges will be made');
        }
        
        // Get all active AutoPay subscriptions due for renewal
        $dueSubscriptions = PurchaseHistory::where('is_autopay_enabled', true)
            ->where('autopay_status', 'ACTIVE')
            ->where('next_autopay_date', '<=', now())
            ->whereNotNull('phonepe_subscription_id')
            ->get();
        
        $this->info("Found {$dueSubscriptions->count()} subscriptions due for renewal");
        
        if ($dueSubscriptions->isEmpty()) {
            $this->info('No subscriptions due for renewal');
            return 0;
        }
        
        $service = new PhonePeAutoPayService();
        $successCount = 0;
        $failCount = 0;
        
        foreach ($dueSubscriptions as $purchase) {
            $this->line("Processing purchase #{$purchase->id} - User: {$purchase->user_id}");
            
            try {
                if ($testMode) {
                    $this->info("  [TEST] Would charge ₹{$purchase->amount} for subscription {$purchase->phonepe_subscription_id}");
                    $successCount++;
                    continue;
                }
                
                // Trigger auto-debit
                $result = $service->triggerAutoDebit(
                    $purchase->phonepe_subscription_id,
                    $purchase->amount
                );
                
                if ($result['success']) {
                    $this->info("  ✅ AutoPay successful - ₹{$purchase->amount} charged");
                    
                    // Update purchase history
                    $purchase->update([
                        'autopay_count' => $purchase->autopay_count + 1,
                        'next_autopay_date' => $this->calculateNextPaymentDate($purchase),
                        'updated_at' => now()
                    ]);
                    
                    $successCount++;
                } else {
                    $this->error("  ❌ AutoPay failed: " . ($result['error'] ?? 'Unknown error'));
                    
                    // Log failure
                    Log::error('PhonePe AutoPay Failed', [
                        'purchase_id' => $purchase->id,
                        'subscription_id' => $purchase->phonepe_subscription_id,
                        'error' => $result['error'] ?? 'Unknown'
                    ]);
                    
                    $failCount++;
                }
                
            } catch (\Exception $e) {
                $this->error("  ❌ Error: " . $e->getMessage());
                
                Log::error('PhonePe AutoPay Exception', [
                    'purchase_id' => $purchase->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $failCount++;
            }
            
            // Small delay between requests
            sleep(1);
        }
        
        $this->newLine();
        $this->info("Processing complete!");
        $this->info("✅ Successful: {$successCount}");
        $this->info("❌ Failed: {$failCount}");
        
        return 0;
    }
    
    /**
     * Calculate next payment date based on subscription plan
     */
    private function calculateNextPaymentDate($purchase)
    {
        // Get subscription details
        $subscription = \App\Models\ManageSubscription::find($purchase->subscription_id);
        
        if (!$subscription) {
            return now()->addMonth(); // Default to 1 month
        }
        
        $months = $subscription->duration_months ?? 1;
        return now()->addMonths($months);
    }
}
