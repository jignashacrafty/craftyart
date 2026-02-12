<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\PhonePeTransaction;
use App\Models\PhonePeNotification;

class PhonePeWebhookController extends Controller
{
    /**
     * Handle PhonePe webhook notifications
     */
    public function handleWebhook(Request $request)
    {
        try {
            // Log the incoming webhook
            Log::info('PhonePe Webhook Received', [
                'headers' => $request->headers->all(),
                'body' => $request->all(),
                'raw_body' => $request->getContent()
            ]);
            
            $payload = $request->all();
            
            // Extract data from webhook
            $merchantOrderId = $payload['merchantOrderId'] ?? $payload['data']['merchantOrderId'] ?? null;
            $merchantSubscriptionId = $payload['merchantSubscriptionId'] ?? $payload['data']['merchantSubscriptionId'] ?? null;
            $phonepeOrderId = $payload['orderId'] ?? $payload['data']['orderId'] ?? null;
            $phonepeTransactionId = $payload['transactionId'] ?? $payload['data']['transactionId'] ?? null;
            $state = $payload['state'] ?? $payload['data']['state'] ?? null;
            $amount = isset($payload['amount']) ? $payload['amount'] / 100 : (isset($payload['data']['amount']) ? $payload['data']['amount'] / 100 : null);
            $eventType = $payload['event'] ?? $payload['type'] ?? 'WEBHOOK_RECEIVED';
            
            // Find transaction
            $transaction = null;
            if ($merchantOrderId) {
                $transaction = PhonePeTransaction::where('merchant_order_id', $merchantOrderId)->first();
            } elseif ($merchantSubscriptionId) {
                $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            } elseif ($phonepeOrderId) {
                $transaction = PhonePeTransaction::where('phonepe_order_id', $phonepeOrderId)->first();
            }
            
            // Determine notification type based on event and state
            $notificationType = $this->determineNotificationType($eventType, $state);
            
            // Create notification record
            $notification = PhonePeNotification::create([
                'merchant_order_id' => $merchantOrderId ?? ($transaction ? $transaction->merchant_order_id : null),
                'merchant_subscription_id' => $merchantSubscriptionId ?? ($transaction ? $transaction->merchant_subscription_id : null),
                'phonepe_order_id' => $phonepeOrderId,
                'phonepe_transaction_id' => $phonepeTransactionId,
                'notification_type' => $notificationType,
                'event_type' => $eventType,
                'amount' => $amount,
                'status' => $state,
                'payment_method' => $payload['paymentMethod'] ?? $payload['data']['paymentMethod'] ?? 'UPI',
                'webhook_payload' => $payload,
                'response_data' => $payload,
                'is_processed' => false,
                'notes' => 'Webhook received from PhonePe'
            ]);
            
            // Update transaction if found
            if ($transaction) {
                $this->updateTransaction($transaction, $payload, $state, $notificationType);
                
                // Mark notification as processed
                $notification->is_processed = true;
                $notification->processed_at = now();
                $notification->save();
            }
            
            // Return success response to PhonePe
            return response()->json([
                'success' => true,
                'message' => 'Webhook received and processed',
                'notification_id' => $notification->id
            ], 200);
            
        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Processing Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Still return success to PhonePe to avoid retries
            return response()->json([
                'success' => true,
                'message' => 'Webhook received but processing failed'
            ], 200);
        }
    }
    
    /**
     * Determine notification type based on event and state
     */
    private function determineNotificationType($eventType, $state)
    {
        // Map event types to notification types
        $eventMap = [
            'SUBSCRIPTION_SETUP_COMPLETED' => 'MANDATE_APPROVED',
            'SUBSCRIPTION_SETUP_FAILED' => 'MANDATE_REJECTED',
            'SUBSCRIPTION_REDEMPTION_COMPLETED' => 'PAYMENT_SUCCESS',
            'SUBSCRIPTION_REDEMPTION_FAILED' => 'PAYMENT_FAILED',
            'SUBSCRIPTION_CANCELLED' => 'SUBSCRIPTION_CANCELLED',
            'PRE_DEBIT_NOTIFICATION' => 'PRE_DEBIT',
        ];
        
        if (isset($eventMap[$eventType])) {
            return $eventMap[$eventType];
        }
        
        // Fallback to state-based determination
        $stateMap = [
            'ACTIVE' => 'MANDATE_APPROVED',
            'COMPLETED' => 'PAYMENT_SUCCESS',
            'FAILED' => 'PAYMENT_FAILED',
            'CANCELLED' => 'SUBSCRIPTION_CANCELLED',
        ];
        
        return $stateMap[$state] ?? 'WEBHOOK_RECEIVED';
    }
    
    /**
     * Update transaction based on webhook data
     */
    private function updateTransaction($transaction, $payload, $state, $notificationType)
    {
        // Update basic fields
        if ($state) {
            $transaction->status = $state;
            $transaction->payment_state = $state;
        }
        
        // Update PhonePe IDs if available
        if (isset($payload['orderId']) || isset($payload['data']['orderId'])) {
            $transaction->phonepe_order_id = $payload['orderId'] ?? $payload['data']['orderId'];
        }
        
        if (isset($payload['transactionId']) || isset($payload['data']['transactionId'])) {
            $transaction->phonepe_transaction_id = $payload['transactionId'] ?? $payload['data']['transactionId'];
        }
        
        // Update AutoPay status
        if ($state === 'ACTIVE') {
            $transaction->is_autopay_active = true;
        } elseif (in_array($state, ['CANCELLED', 'FAILED'])) {
            $transaction->is_autopay_active = false;
        }
        
        // Update AutoPay count for successful payments
        if ($notificationType === 'PAYMENT_SUCCESS') {
            $transaction->autopay_count = $transaction->autopay_count + 1;
            $transaction->last_autopay_at = now();
            $transaction->next_autopay_at = now()->addMonth();
        }
        
        // Merge webhook data into response_data
        $transaction->response_data = array_merge(
            $transaction->response_data ?? [],
            ['webhook_' . time() => $payload]
        );
        
        $transaction->save();
        
        Log::info('Transaction updated from webhook', [
            'transaction_id' => $transaction->id,
            'merchant_order_id' => $transaction->merchant_order_id,
            'new_status' => $state
        ]);
    }
}
