<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\PhonePeToken;
use App\Models\PhonePeAutoPayTestHistory;
use App\Models\PhonePeTransaction;
use App\Models\PhonePeNotification;

class PhonePeSimplePaymentTestController extends Controller
{
    protected $clientId;
    protected $clientSecret;
    protected $clientVersion;
    protected $merchantUserId;
    
    public function __construct()
    {
        // NEW Production credentials
        $this->clientId = "SU2512031928441979485878";
        $this->clientSecret = "04652cf1-d98d-4f48-8ae8-0ecf60fac76f";
        $this->clientVersion = "1";
        $this->merchantUserId = "M22EOXLUSO1LA";
    }
    
    /**
     * Get or generate PhonePe OAuth access token
     */
    private function getAccessToken()
    {
        // Check cache first
        $cachedToken = Cache::get('phonepe_access_token');
        if ($cachedToken) {
            Log::info('Using cached PhonePe token');
            return $cachedToken;
        }
        
        // Generate new token
        $accessTokenUrl = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        
        try {
            Log::info('Generating new PhonePe OAuth token');
            
            $response = Http::asForm()->post($accessTokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'client_version' => $this->clientVersion,
                'grant_type' => 'client_credentials',
            ]);
            
            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                Log::error('PhonePe OAuth Token Generation Failed', [
                    'response' => $data,
                    'client_id' => $this->clientId
                ]);
                throw new \Exception('PhonePe OAuth failed: ' . json_encode($data));
            }
            
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;
            
            // Cache token for 55 minutes (5 minutes before expiry)
            Cache::put('phonepe_access_token', $accessToken, ($expiresIn - 300));
            
            Log::info('New PhonePe access token generated', [
                'expires_in' => $expiresIn
            ]);
            
            return $accessToken;
            
        } catch (\Exception $e) {
            Log::error('PhonePe Access Token Generation Exception', [
                'error' => $e->getMessage(),
                'client_id' => $this->clientId
            ]);
            throw $e;
        }
    }
    
    /**
     * Send payment request using OAuth authentication (like your working project)
     */
    public function sendPaymentRequest(Request $request)
    {
        try {
            $upiId = $request->input('upi_id', 'vrajsurani606@okaxis');
            $amount = $request->input('amount', 1);
            $mobile = $request->input('mobile', '9724085965');
            
            // Get OAuth token
            $token = $this->getAccessToken();
            
            $merchantOrderId = "MO" . uniqid() . time();
            $merchantSubscriptionId = "MS" . uniqid() . time();
            
            // Using SUBSCRIPTION_SETUP like your working project
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => $amount * 100,
                "expireAt" => now()->addMinutes(10)->timestamp * 1000,
                "metaInfo" => [
                    "udf1" => "test_customer",
                    "udf2" => "Test User",
                    "udf3" => $mobile
                ],
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_SETUP",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => $amount * 100,
                    "recurringAmount" => $amount * 100,
                    "frequency" => "Monthly",
                    "expireAt" => now()->addYears(1)->timestamp * 1000,
                    "paymentMode" => [
                        "type" => "UPI_COLLECT",
                        "details" => [
                            "type" => "VPA",
                            "vpa" => $upiId
                        ]
                    ]
                ],
                "deviceContext" => [
                    "deviceOS" => "ANDROID"
                ]
            ];
            
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/setup";
            
            Log::info('Sending PhonePe Payment Request (OAuth)', [
                'url' => $url,
                'merchant_order_id' => $merchantOrderId,
                'upi_id' => $upiId,
                'amount' => $amount,
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('PhonePe Payment Response (OAuth)', [
                'status_code' => $response->status(),
                'response' => $data
            ]);
            
            // Store in history table (old table for testing)
            if (!empty($data['orderId'])) {
                PhonePeAutoPayTestHistory::create([
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'],
                    'upi_id' => $upiId,
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'status' => 'PENDING',
                    'subscription_state' => $data['state'] ?? 'PENDING',
                    'request_payload' => $payload,
                    'response_data' => $data,
                    'notes' => 'AutoPay request sent successfully'
                ]);
                
                // Store in new transactions table (for admin panel)
                PhonePeTransaction::create([
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'],
                    'phonepe_transaction_id' => $data['transactionId'] ?? null,
                    'transaction_type' => 'SUBSCRIPTION_SETUP',
                    'upi_id' => $upiId,
                    'mobile' => $mobile,
                    'amount' => $amount,
                    'status' => 'PENDING',
                    'payment_state' => $data['state'] ?? 'PENDING',
                    'is_autopay_active' => false,
                    'autopay_count' => 0,
                    'request_payload' => $payload,
                    'response_data' => $data,
                    'notes' => 'AutoPay subscription setup request sent'
                ]);
                
                // Create notification for setup request
                PhonePeNotification::create([
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'],
                    'phonepe_transaction_id' => $data['transactionId'] ?? null,
                    'notification_type' => 'SUBSCRIPTION_SETUP',
                    'event_type' => 'SETUP_INITIATED',
                    'amount' => $amount,
                    'status' => 'PENDING',
                    'payment_method' => 'UPI_COLLECT',
                    'webhook_payload' => null,
                    'response_data' => $data,
                    'is_processed' => false,
                    'notes' => 'Subscription setup request sent to user UPI'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Payment request sent successfully!',
                    'data' => [
                        'merchant_order_id' => $merchantOrderId,
                        'merchant_subscription_id' => $merchantSubscriptionId,
                        'order_id' => $data['orderId'],
                        'state' => $data['state'] ?? null,
                        'upi_id' => $upiId,
                        'amount' => $amount,
                        'response' => $data
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? 'Payment request failed',
                'data' => [
                    'merchant_order_id' => $merchantOrderId,
                    'upi_id' => $upiId,
                    'amount' => $amount,
                    'response' => $data
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('PhonePe Payment Request Failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Check subscription status
     */
    public function checkSubscriptionStatus(Request $request)
    {
        try {
            $merchantSubscriptionId = $request->input('merchantSubscriptionId');
            $token = $this->getAccessToken();
            
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
            
            Log::info('Checking subscription status', [
                'subscription_id' => $merchantSubscriptionId
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($url);
            
            $data = $response->json();
            
            // Update history
            $history = PhonePeAutoPayTestHistory::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($history) {
                $history->subscription_state = $data['state'] ?? 'UNKNOWN';
                $history->is_autopay_active = in_array($data['state'] ?? '', ['ACTIVE', 'COMPLETED']);
                $history->response_data = array_merge($history->response_data ?? [], ['status_check' => $data]);
                $history->save();
            }
            
            // Update transaction in new table
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($transaction) {
                $transaction->status = $data['state'] ?? 'UNKNOWN';
                $transaction->payment_state = $data['state'] ?? 'UNKNOWN';
                $transaction->is_autopay_active = in_array($data['state'] ?? '', ['ACTIVE', 'COMPLETED']);
                $transaction->response_data = array_merge($transaction->response_data ?? [], ['status_check_' . time() => $data]);
                $transaction->save();
                
                // Create notification for status check
                PhonePeNotification::create([
                    'merchant_order_id' => $transaction->merchant_order_id,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $transaction->phonepe_order_id,
                    'phonepe_transaction_id' => $data['transactionId'] ?? null,
                    'notification_type' => 'STATUS_CHECK',
                    'event_type' => 'STATUS_UPDATED',
                    'amount' => $transaction->amount,
                    'status' => $data['state'] ?? 'UNKNOWN',
                    'payment_method' => 'UPI_COLLECT',
                    'webhook_payload' => null,
                    'response_data' => $data,
                    'is_processed' => true,
                    'processed_at' => now(),
                    'notes' => 'Manual status check performed'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Check subscription status failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send pre-debit notification
     * 
     * IMPORTANT: PhonePe OAuth API doesn't have a separate pre-debit notification endpoint.
     * Pre-debit notifications are sent automatically by the bank when you trigger a redemption.
     * 
     * For testing purposes, this method will:
     * 1. Verify subscription is ACTIVE
     * 2. Mark pre-debit as sent in our system
     * 3. Inform user that actual SMS will come from bank when real redemption happens
     */
    public function sendPreDebitNotification(Request $request)
    {
        try {
            $merchantSubscriptionId = $request->input('merchantSubscriptionId');
            $amount = $request->input('amount', 1);
            $token = $this->getAccessToken();
            
            // First check if subscription is ACTIVE
            $statusUrl = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
            $statusResponse = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($statusUrl);
            
            $statusData = $statusResponse->json();
            
            if (!isset($statusData['state']) || $statusData['state'] !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'error_message' => 'Subscription must be ACTIVE to send pre-debit notification. Current state: ' . ($statusData['state'] ?? 'UNKNOWN'),
                    'data' => $statusData
                ]);
            }
            
            Log::info('Pre-debit notification check', [
                'subscription_id' => $merchantSubscriptionId,
                'amount' => $amount,
                'subscription_state' => $statusData['state']
            ]);
            
            // Update history
            $history = PhonePeAutoPayTestHistory::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($history) {
                $history->predebit_sent = true;
                $history->predebit_sent_at = now();
                $history->response_data = array_merge($history->response_data ?? [], [
                    'predebit_info' => [
                        'timestamp' => now()->toIso8601String(),
                        'note' => 'Pre-debit SMS will be sent by bank when redemption is triggered',
                        'subscription_state' => $statusData['state'],
                        'subscription_id' => $statusData['subscriptionId'] ?? null
                    ]
                ]);
                $history->save();
            }
            
            // Update transaction
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($transaction) {
                // Create notification for pre-debit info
                PhonePeNotification::create([
                    'merchant_order_id' => $transaction->merchant_order_id,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $transaction->phonepe_order_id,
                    'phonepe_transaction_id' => null,
                    'notification_type' => 'PRE_DEBIT_INFO',
                    'event_type' => 'PRE_DEBIT_READY',
                    'amount' => $amount,
                    'status' => 'READY',
                    'payment_method' => 'UPI_AUTOPAY',
                    'webhook_payload' => null,
                    'response_data' => [
                        'note' => 'Subscription is ACTIVE and ready for redemption',
                        'subscription_state' => $statusData['state'],
                        'subscription_id' => $statusData['subscriptionId'] ?? null,
                        'phonepe_subscription_id' => $statusData['subscriptionId'] ?? null
                    ],
                    'is_processed' => true,
                    'processed_at' => now(),
                    'notes' => 'Pre-debit notification will be sent by bank when you trigger auto-debit. Click "💳 Debit" button to trigger payment.'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "✅ Subscription is ACTIVE and ready!\n\n📱 Pre-debit SMS will be sent by your bank when you trigger the payment.\n\n🎯 Click '💳 Debit' button to trigger auto-payment now.",
                'phonepe_subscription_id' => $statusData['subscriptionId'] ?? null,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'subscription_state' => $statusData['state'],
                'note' => 'ℹ️ PhonePe OAuth API: Pre-debit notifications are sent automatically by the bank when redemption is triggered. There is no separate pre-debit API endpoint.',
                'next_step' => 'Click "💳 Debit" button to trigger immediate payment and receive bank SMS',
                'data' => $statusData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Pre-debit notification check failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
                'note' => 'Pre-debit notifications are sent automatically by bank when redemption is triggered'
            ], 500);
        }
    }
    
    /**
     * Trigger auto-debit (manual redemption)
     */
    public function triggerAutoDebit(Request $request)
    {
        try {
            $merchantSubscriptionId = $request->input('merchantSubscriptionId');
            $amount = $request->input('amount', 1);
            $token = $this->getAccessToken();
            
            // First check if subscription is ACTIVE
            $statusUrl = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
            $statusResponse = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($statusUrl);
            
            $statusData = $statusResponse->json();
            
            if (!isset($statusData['state']) || $statusData['state'] !== 'ACTIVE') {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription must be ACTIVE to trigger auto-debit. Current state: ' . ($statusData['state'] ?? 'UNKNOWN'),
                    'data' => $statusData
                ]);
            }
            
            $merchantOrderId = "MO_REDEEM_" . uniqid() . time();
            
            // Correct payload structure for redemption
            $payload = [
                'merchantOrderId' => $merchantOrderId,
                'amount' => $amount * 100,
                'expireAt' => now()->addMinutes(10)->timestamp * 1000,
                'paymentFlow' => [
                    'type' => 'SUBSCRIPTION_REDEMPTION',
                    'merchantSubscriptionId' => $merchantSubscriptionId,
                    'redemptionRetryStrategy' => 'STANDARD',
                    'autoDebit' => true
                ],
                'deviceContext' => [
                    'deviceOS' => 'ANDROID'
                ]
            ];
            
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/redeem";
            
            Log::info('Triggering manual auto-debit', [
                'subscription_id' => $merchantSubscriptionId,
                'amount' => $amount,
                'merchant_order_id' => $merchantOrderId,
                'subscription_state' => $statusData['state'],
                'payload' => $payload
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('Auto-debit response', [
                'status_code' => $response->status(),
                'response' => $data
            ]);
            
            $success = $response->successful() && isset($data['orderId']);
            
            // Update history
            $history = PhonePeAutoPayTestHistory::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($history) {
                if ($success) {
                    $history->autopay_count = $history->autopay_count + 1;
                    $history->last_autopay_at = now();
                    $history->next_autopay_at = now()->addMonth();
                }
                $history->response_data = array_merge($history->response_data ?? [], [
                    'autodebit_attempt_' . time() => [
                        'success' => $success,
                        'merchant_order_id' => $merchantOrderId,
                        'response' => $data,
                        'timestamp' => now()->toIso8601String()
                    ]
                ]);
                $history->save();
            }
            
            // Update transaction
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($transaction) {
                if ($success) {
                    $transaction->autopay_count = $transaction->autopay_count + 1;
                    $transaction->last_autopay_at = now();
                    $transaction->next_autopay_at = now()->addMonth();
                    $transaction->status = 'COMPLETED';
                    $transaction->payment_state = $data['state'] ?? 'COMPLETED';
                }
                $transaction->response_data = array_merge($transaction->response_data ?? [], [
                    'redemption_' . time() => $data
                ]);
                $transaction->save();
                
                // Create notification for auto-debit
                PhonePeNotification::create([
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'phonepe_transaction_id' => $data['transactionId'] ?? null,
                    'notification_type' => $success ? 'PAYMENT_SUCCESS' : 'PAYMENT_FAILED',
                    'event_type' => 'SUBSCRIPTION_REDEMPTION',
                    'amount' => $amount,
                    'status' => $success ? 'SUCCESS' : 'FAILED',
                    'payment_method' => 'UPI_AUTOPAY',
                    'webhook_payload' => null,
                    'response_data' => $data,
                    'is_processed' => $success,
                    'processed_at' => $success ? now() : null,
                    'notes' => $success ? 'Auto-debit payment successful' : 'Auto-debit payment failed'
                ]);
            }
            
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Auto-debit triggered successfully! Check your phone for payment notification.' : ($data['message'] ?? 'Auto-debit failed'),
                'merchant_order_id' => $merchantOrderId,
                'phonepe_order_id' => $data['orderId'] ?? null,
                'state' => $data['state'] ?? null,
                'data' => $data
            ]);
            
        } catch (\Exception $e) {
            Log::error('Auto-debit trigger failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get AutoPay test history
     */
    public function getHistory(Request $request)
    {
        try {
            $history = PhonePeAutoPayTestHistory::orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $history
            ]);
            
        } catch (\Exception $e) {
            Log::error('Get history failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Simulate auto-debit for testing (PhonePe doesn't allow manual redemption)
     */
    public function simulateAutoDebit(Request $request)
    {
        try {
            $merchantSubscriptionId = $request->input('merchantSubscriptionId');
            $amount = $request->input('amount', 1);
            
            Log::info('Simulating auto-debit', [
                'subscription_id' => $merchantSubscriptionId,
                'amount' => $amount
            ]);
            
            // Find transaction
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            
            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found'
                ], 404);
            }
            
            if (!$transaction->is_autopay_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'AutoPay is not active. Please approve the mandate first.'
                ], 400);
            }
            
            // Simulate successful payment
            $transaction->autopay_count = $transaction->autopay_count + 1;
            $transaction->last_autopay_at = now();
            $transaction->next_autopay_at = now()->addMonth();
            $transaction->save();
            
            // Create notification
            PhonePeNotification::create([
                'merchant_order_id' => 'MO_SIM_' . time(),
                'merchant_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => 'OMO_SIM_' . time(),
                'phonepe_transaction_id' => 'TXN_SIM_' . time(),
                'notification_type' => 'PAYMENT_SUCCESS',
                'event_type' => 'SUBSCRIPTION_REDEMPTION_SIMULATED',
                'amount' => $amount,
                'status' => 'SUCCESS',
                'payment_method' => 'UPI_AUTOPAY',
                'response_data' => [
                    'simulated' => true,
                    'timestamp' => now()->toIso8601String(),
                    'note' => 'This is a simulated payment for testing purposes'
                ],
                'is_processed' => true,
                'processed_at' => now(),
                'notes' => '⚠️ SIMULATED auto-debit for testing purposes. In production, PhonePe handles this automatically.'
            ]);
            
            Log::info('Auto-debit simulated successfully', [
                'subscription_id' => $merchantSubscriptionId,
                'autopay_count' => $transaction->autopay_count
            ]);
            
            return response()->json([
                'success' => true,
                'message' => '✅ Auto-debit simulated successfully!',
                'data' => [
                    'autopay_count' => $transaction->autopay_count,
                    'last_payment' => $transaction->last_autopay_at->format('d M Y, h:i A'),
                    'next_payment' => $transaction->next_autopay_at->format('d M Y, h:i A'),
                    'amount' => '₹' . number_format($amount, 2)
                ],
                'note' => '⚠️ This is a simulation. In production, PhonePe automatically debits based on schedule.'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Simulate auto-debit failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
