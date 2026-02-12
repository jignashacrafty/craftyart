<?php

namespace App\Jobs;

use App\Models\PhonePeSubscription;
use App\Http\Controllers\Api\PhonePePreDebitController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPhonePePreDebitNotification implements ShouldQueue
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
            Log::info('📤 Processing pre-debit notification', [
                'subscription_id' => $this->subscription->id,
                'merchant_subscription_id' => $this->subscription->merchant_subscription_id
            ]);

            $controller = app(PhonePePreDebitController::class);
            $request = new \Illuminate\Http\Request();
            $request->merge([
                'merchant_subscription_id' => $this->subscription->merchant_subscription_id
            ]);

            $controller->sendNotification($request);

        } catch (\Exception $e) {
            Log::error('❌ Pre-debit notification job failed', [
                'subscription_id' => $this->subscription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
