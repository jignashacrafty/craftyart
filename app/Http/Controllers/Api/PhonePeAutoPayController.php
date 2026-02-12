<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhonePeSubscription;
use App\Models\PhonePeAutoPayTransaction;
use App\Models\Order;
use App\Models\UserData;
use App\Services\PhonePeTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PhonePeAutoPayController extends Controller
{
    protected $tokenService;
    protected $production;
    
    public function __construct(PhonePeTokenService $tokenService)
    {
        $this->tokenService = $tokenService;
        
        // Get environment from payment configuration
        $config = \App\Models\PaymentConfiguration::where('gateway', 'PhonePe')
            ->where('is_active', 1)
            ->first();
            
        if ($config && isset($config->credentials['environment'])) {
            $this->production = ($config->credentials['environment'] === 'production');
        } else {
            $this->production = false; // Default to sandbox
        }
    }
    
    /**
     * Setup UPI AutoPay subscription
     * POST /api/phonepe/autopay/setup
     */
    public function setupSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'upi' => 'nullable|string',
            'target_app' => 'nullable|string',
        ]);
        
        try {
            $user = UserData::where('uid', $request->user_id)->first();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $amount = $request->amount;
            $merchantOrderId = "MO_SETUP_" . uniqid() . time();
            $merchantSubscriptionId = "MS_" . uniqid() . time();
            
            // Create order first
            $order = Order::create([
                'user_id' => $request->user_id,
                'plan_id' => $request->plan_id,
                'crafty_id' => Order::generateCraftyId(),
                'amount' => $amount,
                'currency' => 'INR',
                'status' => 'pending',
                'type' => 'new_sub',
                'razorpay_order_id' => 'PHONEPE_' . uniqid(), // Unique identifier for PhonePe orders
            ]);
            
            $token = $this->tokenService->getAccessToken();
            
            // Determine payment mode - for sandbox, use UPI_INTENT
            $payload = [
                "merchantId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
                "merchantOrderId" => $merchantOrderId,
                "merchantUserId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
                "amount" => $amount * 100,
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
                    "message" => "Monthly Subscription",
                    "merchantUrls" => [
                        "redirectUrl" => url('/api/phonepe/autopay/callback'),
                        "cancelRedirectUrl" => url('/api/phonepe/autopay/callback'),
                    ],
                    "subscriptionDetails" => [
                        "subscriptionType" => "RECURRING",
                        "merchantSubscriptionId" => $merchantSubscriptionId,
                        "authWorkflowType" => "TRANSACTION",
                        "amountType" => "FIXED",
                        "maxAmount" => $amount * 100,
                        "recurringAmount" => $amount * 100,
                        "frequency" => "Monthly",
                        "productType" => "UPI_MANDATE",
                        "expireAt" => now()->addMonths(12)->timestamp * 1000,
                    ],
                ],
                "expireAfter" => 3000,
            ];
            
            // Use checkout API, not subscriptions API
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/checkout/v2/pay"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay";
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('📤 PhonePe AutoPay Setup Response', [
                'merchant_order_id' => $merchantOrderId,
                'http_code' => $response->status(),
                'response' => $data,
                'url' => $url
            ]);
            
            // Check for authorization errors
            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
                return response()->json([
                    'success' => false,
                    'message' => 'PhonePe Authorization Failed',
                    'error' => 'Authorization failed. Please check credentials.',
                    'details' => $data
                ], 400);
            }
            
            // Check for successful response (checkout API returns redirectUrl)
            if ($response->successful() && isset($data['redirectUrl'])) {
                // Create subscription record
                PhonePeSubscription::create([
                    'user_id' => $request->user_id,
                    'order_id' => $order->id,
                    'plan_id' => $request->plan_id,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'merchant_order_id' => $merchantOrderId,
                    'phonepe_subscription_id' => $data['orderId'] ?? null,
                    'amount' => $amount,
                    'currency' => 'INR',
                    'frequency' => 'Monthly',
                    'max_amount' => $amount,
                    'start_date' => now()->toDateString(),
                    'next_billing_date' => now()->addMonth()->toDateString(),
                    'status' => 'PENDING', // Will be ACTIVE after user completes payment
                    'subscription_status' => $data['state'] ?? 'PENDING',
                    'metadata' => [
                        'setup_payload' => $payload,
                        'setup_response' => $data
                    ]
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription setup initiated successfully',
                    'data' => [
                        'merchant_order_id' => $merchantOrderId,
                        'merchant_subscription_id' => $merchantSubscriptionId,
                        'phonepe_order_id' => $data['orderId'] ?? null,
                        'redirect_url' => $data['redirectUrl'],
                        'state' => $data['state'] ?? 'PENDING',
                        'expire_at' => $data['expireAt'] ?? null
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Subscription setup failed',
                'error' => $data
            ], 400);
            
        } catch (\Exception $e) {
            Log::error('❌ PhonePe AutoPay Setup Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Setup failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancel subscription
     * POST /api/phonepe/autopay/cancel
     */
    public function cancelSubscription(Request $request)
    {
        $request->validate([
            'merchant_subscription_id' => 'required|string'
        ]);
        
        try {
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $request->merchant_subscription_id)->first();
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }
            
            $token = $this->tokenService->getAccessToken();
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/{$request->merchant_subscription_id}/cancel"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/{$request->merchant_subscription_id}/cancel";
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url);
            
            if ($response->status() === 204 || $response->successful()) {
                $subscription->status = 'CANCELLED';
                $subscription->subscription_status = 'CANCELLED';
                $subscription->save();
                
                Log::info('✅ Subscription cancelled', [
                    'subscription_id' => $subscription->id,
                    'merchant_subscription_id' => $request->merchant_subscription_id
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Subscription cancelled successfully'
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed',
                'error' => $response->json()
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error('❌ Subscription cancellation exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Cancellation failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get subscription status
     * GET /api/phonepe/autopay/status/{merchantSubscriptionId}
     */
    public function getSubscriptionStatus($merchantSubscriptionId)
    {
        try {
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $merchantSubscriptionId)->first();
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subscription not found'
                ], 404);
            }
            
            $token = $this->tokenService->getAccessToken();
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($url);
            
            $data = $response->json();
            
            if ($response->successful()) {
                // Update local status
                if (isset($data['state'])) {
                    $subscription->subscription_status = $data['state'];
                    $subscription->save();
                }
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'local_status' => $subscription->status,
                        'phonepe_status' => $data['state'] ?? null,
                        'details' => $data
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Status check failed',
                'error' => $data
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error('❌ Status check exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Status check failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Trigger manual redemption (fallback)
     * POST /api/phonepe/autopay/redeem
     */
    public function triggerManualRedemption(Request $request)
    {
        $request->validate([
            'merchant_subscription_id' => 'required|string'
        ]);
        
        try {
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $request->merchant_subscription_id)
                ->where('status', 'ACTIVE')
                ->first();
            
            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Active subscription not found'
                ], 404);
            }
            
            // Check if already processed today
            $today = now()->toDateString();
            $alreadyProcessed = PhonePeAutoPayTransaction::where('subscription_id', $subscription->id)
                ->where('transaction_type', 'manual')
                ->whereDate('created_at', $today)
                ->exists();
            
            if ($alreadyProcessed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Manual redemption already triggered today'
                ], 400);
            }
            
            $token = $this->tokenService->getAccessToken();
            $merchantOrderId = "MO_MANUAL_" . uniqid() . time();
            
            $payload = [
                'merchantOrderId' => $merchantOrderId,
                'amount' => (int) ($subscription->amount * 100),
                'paymentFlow' => [
                    'type' => 'SUBSCRIPTION_REDEMPTION',
                    'merchantSubscriptionId' => $request->merchant_subscription_id,
                    'redemptionRetryStrategy' => 'STANDARD',
                    'autoDebit' => true
                ]
            ];
            
            // Use correct URL based on environment
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/redeem"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/redeem";
            
            Log::info('🔄 Triggering manual redemption', [
                'subscription_id' => $subscription->id,
                'merchant_order_id' => $merchantOrderId,
                'url' => $url
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('📤 PhonePe Redemption Response', [
                'http_code' => $response->status(),
                'response' => $data,
                'body' => $response->body()
            ]);
            
            // Handle 204 No Content (successful but no body)
            if ($response->status() === 204) {
                return response()->json([
                    'success' => false,
                    'message' => 'PhonePe Subscription Redemption API Not Available in Sandbox',
                    'error' => 'The subscription redemption/auto-debit API is only available in production environment. In sandbox, you can only test subscription setup.',
                    'details' => [
                        'subscription_id' => $subscription->id,
                        'merchant_subscription_id' => $request->merchant_subscription_id,
                        'amount' => $subscription->amount,
                        'next_billing_date' => $subscription->next_billing_date,
                        'http_code' => 204,
                        'note' => 'To test auto-debit, you need production credentials and a live UPI mandate.'
                    ]
                ], 400);
            }
            
            // Check for authorization errors (sandbox limitation)
            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
                return response()->json([
                    'success' => false,
                    'message' => 'PhonePe Subscription Redemption API Not Available in Sandbox',
                    'error' => 'The subscription redemption/auto-debit API is only available in production environment. In sandbox, you can only test subscription setup.',
                    'details' => [
                        'subscription_id' => $subscription->id,
                        'merchant_subscription_id' => $request->merchant_subscription_id,
                        'amount' => $subscription->amount,
                        'next_billing_date' => $subscription->next_billing_date,
                        'note' => 'To test auto-debit, you need production credentials and a live UPI mandate.'
                    ]
                ], 400);
            }
            
            if ($response->successful() && isset($data['orderId'])) {
                // Create transaction record
                PhonePeAutoPayTransaction::create([
                    'subscription_id' => $subscription->id,
                    'merchant_subscription_id' => $request->merchant_subscription_id,
                    'merchant_order_id' => $merchantOrderId,
                    'amount' => $subscription->amount,
                    'currency' => $subscription->currency,
                    'transaction_type' => 'manual',
                    'status' => 'pending',
                    'is_autopay' => false
                ]);
                
                Log::info('✅ Manual redemption triggered', [
                    'subscription_id' => $subscription->id,
                    'merchant_order_id' => $merchantOrderId
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Manual redemption triggered',
                    'data' => [
                        'merchant_order_id' => $merchantOrderId,
                        'phonepe_order_id' => $data['orderId']
                    ]
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Manual redemption failed',
                'error' => $data
            ], $response->status());
            
        } catch (\Exception $e) {
            Log::error('❌ Manual redemption exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Manual redemption failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
