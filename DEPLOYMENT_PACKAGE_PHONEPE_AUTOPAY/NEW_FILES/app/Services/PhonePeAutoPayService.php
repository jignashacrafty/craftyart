<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\PhonePeTransaction;
use App\Models\PhonePeNotification;
use App\Models\PurchaseHistory;
use App\Models\ManageSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PhonePeAutoPayService
{
    protected $config;
    protected $clientId;
    protected $clientSecret;
    protected $merchantUserId;
    protected $isProduction;
    
    public function __construct()
    {
        $this->loadConfiguration();
    }
    
    /**
     * Load PhonePe configuration from payment_configurations table
     */
    private function loadConfiguration()
    {
        // Get PhonePe configuration from database
        $config = DB::table('payment_configurations')
            ->where('gateway', 'PhonePe')
            ->where('is_active', 1)
            ->first();
        
        if (!$config) {
            throw new \Exception('PhonePe configuration not found or inactive');
        }
        
        $this->config = $config;
        $credentials = json_decode($config->credentials, true);
        
        // Extract credentials
        $this->clientId = $credentials['client_id'] ?? null;
        $this->clientSecret = $credentials['client_secret'] ?? null;
        $this->merchantUserId = $credentials['merchant_user_id'] ?? null;
        $this->isProduction = true; // Always production for live credentials
        
        if (!$this->clientId || !$this->clientSecret || !$this->merchantUserId) {
            throw new \Exception('PhonePe credentials incomplete');
        }
    }
    
    /**
     * Get OAuth access token
     */
    public function getAccessToken()
    {
        // Check cache first
        $cacheKey = 'phonepe_oauth_token_' . $this->clientId;
        $cachedToken = Cache::get($cacheKey);
        
        if ($cachedToken) {
            return $cachedToken;
        }
        
        // Generate new token
        $url = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        
        try {
            $response = Http::asForm()->post($url, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'client_version' => '1',
                'grant_type' => 'client_credentials',
            ]);
            
            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                throw new \Exception('OAuth token generation failed: ' . json_encode($data));
            }
            
            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 3600;
            
            // Cache token for 55 minutes
            Cache::put($cacheKey, $accessToken, ($expiresIn - 300));
            
            Log::info('PhonePe OAuth token generated', [
                'client_id' => $this->clientId,
                'expires_in' => $expiresIn
            ]);
            
            return $accessToken;
            
        } catch (\Exception $e) {
            Log::error('PhonePe OAuth token generation failed', [
                'error' => $e->getMessage(),
                'client_id' => $this->clientId
            ]);
            throw $e;
        }
    }
    
    /**
     * Setup AutoPay subscription for a purchase
     */
    public function setupSubscription($purchaseHistory, $subscription)
    {
        try {
            $token = $this->getAccessToken();
            
            $merchantOrderId = "MO_" . $purchaseHistory->id . "_" . time();
            $merchantSubscriptionId = "MS_" . $subscription->id . "_" . time();
            
            // Calculate amounts
            $amount = $subscription->price;
            $recurringAmount = $subscription->price;
            
            // Determine frequency based on plan
            $frequency = $this->getFrequency($subscription);
            $expireAt = $this->getExpiryTimestamp($subscription);
            
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => $amount * 100, // Convert to paise
                "expireAt" => now()->addMinutes(10)->timestamp * 1000,
                "redirectUrl" => url('/phonepe/autopay/callback'),
                "redirectMode" => "POST",
                "callbackUrl" => url('/phonepe/autopay/webhook'),
                "metaInfo" => [
                    "udf1" => "subscription_" . $subscription->id,
                    "udf2" => $subscription->plan_name,
                    "udf3" => $purchaseHistory->user_id
                ],
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_SETUP",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => $recurringAmount * 100,
                    "recurringAmount" => $recurringAmount * 100,
                    "frequency" => $frequency,
                    "expireAt" => $expireAt,
                    "paymentMode" => [
                        "type" => "UPI_INTENT"
                    ]
                ],
                "deviceContext" => [
                    "deviceOS" => "ANDROID"
                ]
            ];
            
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/setup";
            
            Log::info('PhonePe AutoPay Setup Request', [
                'purchase_id' => $purchaseHistory->id,
                'subscription_id' => $subscription->id,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $amount
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('PhonePe AutoPay Setup Response', [
                'status_code' => $response->status(),
                'response' => $data
            ]);
            
            // Store transaction
            $transaction = PhonePeTransaction::create([
                'user_id' => $purchaseHistory->user_id,
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => $data['orderId'] ?? null,
                'phonepe_transaction_id' => $data['transactionId'] ?? null,
                'transaction_type' => 'SUBSCRIPTION_SETUP',
                'upi_id' => null,
                'mobile' => $purchaseHistory->contact_no ?? null,
                'amount' => $amount,
                'status' => 'PENDING',
                'payment_state' => $data['state'] ?? 'PENDING',
                'is_autopay_active' => false,
                'autopay_count' => 0,
                'request_payload' => $payload,
                'response_data' => $data,
                'notes' => 'AutoPay setup for subscription #' . $subscription->id
            ]);
            
            // Create notification
            PhonePeNotification::create([
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => $data['orderId'] ?? null,
                'notification_type' => 'SUBSCRIPTION_SETUP',
                'event_type' => 'SETUP_INITIATED',
                'amount' => $amount,
                'status' => 'PENDING',
                'payment_method' => 'UPI_COLLECT',
                'response_data' => $data,
                'is_processed' => false,
                'notes' => 'AutoPay subscription setup initiated'
            ]);
            
            // Update purchase history with subscription IDs
            $purchaseHistory->update([
                'phonepe_merchant_order_id' => $merchantOrderId,
                'phonepe_subscription_id' => $merchantSubscriptionId,
                'phonepe_order_id' => $data['orderId'] ?? null
            ]);
            
            // Extract payment URL from response
            $paymentUrl = null;
            if (isset($data['data']['instrumentResponse']['redirectInfo']['url'])) {
                $paymentUrl = $data['data']['instrumentResponse']['redirectInfo']['url'];
            } elseif (isset($data['data']['redirectInfo']['url'])) {
                $paymentUrl = $data['data']['redirectInfo']['url'];
            } elseif (isset($data['instrumentResponse']['redirectInfo']['url'])) {
                $paymentUrl = $data['instrumentResponse']['redirectInfo']['url'];
            }
            
            // Log payment URL for debugging
            Log::info('PhonePe Payment URL', [
                'merchant_order_id' => $merchantOrderId,
                'payment_url' => $paymentUrl,
                'has_url' => !empty($paymentUrl)
            ]);
            
            return [
                'success' => true,
                'transaction' => $transaction,
                'phonepe_response' => $data,
                'payment_url' => $paymentUrl
            ];
            
        } catch (\Exception $e) {
            Log::error('PhonePe AutoPay Setup Failed', [
                'error' => $e->getMessage(),
                'purchase_id' => $purchaseHistory->id ?? null
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Get frequency based on subscription plan
     */
    private function getFrequency($subscription)
    {
        // Map plan duration to PhonePe frequency
        $durationMap = [
            '1' => 'Monthly',
            '3' => 'Quarterly',
            '6' => 'Half-Yearly',
            '12' => 'Yearly'
        ];
        
        $months = $subscription->duration_months ?? 1;
        return $durationMap[$months] ?? 'Monthly';
    }
    
    /**
     * Get expiry timestamp based on subscription
     */
    private function getExpiryTimestamp($subscription)
    {
        $months = $subscription->duration_months ?? 12;
        return now()->addMonths($months)->timestamp * 1000;
    }
    
    /**
     * Generate UPI ID from user data
     */
    private function generateUpiId($purchaseHistory)
    {
        // This should be replaced with actual UPI ID from user
        // For now, return null to prompt user to enter
        return null;
    }
    
    /**
     * Check subscription status
     */
    public function checkStatus($merchantSubscriptionId)
    {
        try {
            $token = $this->getAccessToken();
            $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
            
            Log::info('PhonePe Status Check Request', [
                'subscription_id' => $merchantSubscriptionId,
                'url' => $url
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($url);
            
            $data = $response->json();
            
            Log::info('PhonePe Status Check Response', [
                'subscription_id' => $merchantSubscriptionId,
                'status_code' => $response->status(),
                'response' => $data,
                'response_keys' => array_keys($data ?? [])
            ]);
            
            // Check different possible response structures
            $newStatus = null;
            if (isset($data['state'])) {
                $newStatus = $data['state'];
            } elseif (isset($data['data']['state'])) {
                $newStatus = $data['data']['state'];
            } elseif (isset($data['status'])) {
                $newStatus = $data['status'];
            } elseif (isset($data['subscriptionState'])) {
                $newStatus = $data['subscriptionState'];
            }
            
            if (!$newStatus) {
                Log::warning('Could not find status in PhonePe response', [
                    'response' => $data
                ]);
                $newStatus = 'UNKNOWN';
            }
            
            // Update transaction
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($transaction) {
                $isActive = in_array($newStatus, ['ACTIVE', 'COMPLETED']);
                
                Log::info('Updating Transaction', [
                    'transaction_id' => $transaction->id,
                    'old_status' => $transaction->status,
                    'new_status' => $newStatus,
                    'is_autopay_active' => $isActive
                ]);
                
                $transaction->update([
                    'status' => $newStatus,
                    'payment_state' => $newStatus,
                    'is_autopay_active' => $isActive,
                    'response_data' => array_merge($transaction->response_data ?? [], ['status_check_' . time() => $data])
                ]);
            }
            
            return [
                'success' => true,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('PhonePe Status Check Failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $merchantSubscriptionId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Trigger auto-debit (redemption)
     */
    public function triggerAutoDebit($merchantSubscriptionId, $amount)
    {
        try {
            $token = $this->getAccessToken();
            
            // Check if subscription is ACTIVE
            $statusCheck = $this->checkStatus($merchantSubscriptionId);
            if (!$statusCheck['success'] || ($statusCheck['data']['state'] ?? '') !== 'ACTIVE') {
                throw new \Exception('Subscription must be ACTIVE to trigger auto-debit');
            }
            
            $merchantOrderId = "MO_REDEEM_" . uniqid() . "_" . time();
            
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
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            $success = $response->successful() && isset($data['orderId']);
            
            // Update transaction
            $transaction = PhonePeTransaction::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            if ($transaction && $success) {
                $transaction->update([
                    'autopay_count' => $transaction->autopay_count + 1,
                    'last_autopay_at' => now(),
                    'next_autopay_at' => now()->addMonth(),
                    'status' => 'COMPLETED',
                    'response_data' => array_merge($transaction->response_data ?? [], ['redemption_' . time() => $data])
                ]);
                
                // Create notification
                PhonePeNotification::create([
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'notification_type' => 'PAYMENT_SUCCESS',
                    'event_type' => 'SUBSCRIPTION_REDEMPTION',
                    'amount' => $amount,
                    'status' => 'SUCCESS',
                    'payment_method' => 'UPI_AUTOPAY',
                    'response_data' => $data,
                    'is_processed' => true,
                    'processed_at' => now(),
                    'notes' => 'Auto-debit payment successful'
                ]);
            }
            
            return [
                'success' => $success,
                'data' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('PhonePe Auto-Debit Failed', [
                'error' => $e->getMessage(),
                'subscription_id' => $merchantSubscriptionId
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
