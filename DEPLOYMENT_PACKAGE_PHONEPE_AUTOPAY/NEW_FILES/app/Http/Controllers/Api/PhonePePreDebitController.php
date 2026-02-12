<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PhonePeSubscription;
use App\Models\PhonePePreDebitNotification;
use App\Models\UserData;
use App\Services\PhonePeTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PhonePePreDebitController extends Controller
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
     * Send pre-debit notification
     * POST /api/phonepe/predebit/send
     */
    public function sendNotification(Request $request)
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
            
            $user = UserData::where('uid', $subscription->user_id)->first();
            if (!$user || empty($user->contact_no)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User or contact not found'
                ], 404);
            }
            
            // Check if already sent today
            $today = now()->toDateString();
            $existing = PhonePePreDebitNotification::where('subscription_id', $subscription->id)
                ->whereDate('notification_date', $today)
                ->first();
            
            if ($existing && $existing->isSent()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Notification already sent today'
                ], 400);
            }
            
            $debitDate = $subscription->next_billing_date->toDateString();
            $amount = $subscription->amount;
            
            // Send PhonePe API notification
            $phonePeResult = $this->sendPhonePeNotification($subscription, $amount, $debitDate);
            
            // Send WhatsApp notification
            $whatsAppResult = $this->sendWhatsAppNotification($user, $amount, $debitDate);
            
            // Create or update notification record
            $notification = $existing ?? new PhonePePreDebitNotification();
            $notification->subscription_id = $subscription->id;
            $notification->merchant_subscription_id = $request->merchant_subscription_id;
            $notification->notification_date = $today;
            $notification->debit_date = $debitDate;
            $notification->amount = $amount;
            $notification->user_id = $subscription->user_id;
            $notification->phone = $user->contact_no;
            
            // PhonePe status
            $notification->phonepe_order_id = $phonePeResult['order_id'] ?? null;
            $notification->phonepe_status = $phonePeResult['success'] ? 'sent' : 'failed';
            $notification->phonepe_response = $phonePeResult;
            
            // WhatsApp status
            $notification->whatsapp_status = $whatsAppResult['success'] ? 'sent' : 'failed';
            $notification->whatsapp_response = $whatsAppResult;
            
            // Overall status
            if ($phonePeResult['success'] && $whatsAppResult['success']) {
                $notification->overall_status = 'sent';
            } elseif ($phonePeResult['success'] || $whatsAppResult['success']) {
                $notification->overall_status = 'partial';
            } else {
                $notification->overall_status = 'failed';
            }
            
            $notification->save();
            
            Log::info('✅ Pre-debit notification sent', [
                'subscription_id' => $subscription->id,
                'phonepe_status' => $notification->phonepe_status,
                'whatsapp_status' => $notification->whatsapp_status,
                'overall_status' => $notification->overall_status
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Pre-debit notification sent',
                'data' => [
                    'notification_id' => $notification->id,
                    'phonepe_status' => $notification->phonepe_status,
                    'whatsapp_status' => $notification->whatsapp_status,
                    'overall_status' => $notification->overall_status
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('❌ Pre-debit notification exception', [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Notification failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Send PhonePe API notification
     */
    protected function sendPhonePeNotification($subscription, $amount, $debitDate)
    {
        try {
            $token = $this->tokenService->getAccessToken();
            $merchantOrderId = "MO_PREDEBIT_" . uniqid() . time();
            
            $payload = [
                'merchantOrderId' => $merchantOrderId,
                'amount' => $amount * 100,
                'merchantSubscriptionId' => $subscription->merchant_subscription_id,
                'message' => 'Your subscription will be renewed tomorrow. Amount ₹' . $amount . ' will be debited on ' . $debitDate
            ];
            
            // Use correct URL based on environment
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/notify"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/notify";
            
            Log::info('📤 Sending PhonePe pre-debit notification', [
                'url' => $url,
                'environment' => $this->production ? 'production' : 'sandbox',
                'merchant_subscription_id' => $subscription->merchant_subscription_id,
                'amount' => $amount
            ]);
            
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);
            
            $data = $response->json();
            
            Log::info('📥 PhonePe pre-debit notification response', [
                'http_code' => $response->status(),
                'response' => $data
            ]);
            
            $success = $response->successful() && isset($data['success']) && $data['success'] === true;
            
            return [
                'success' => $success,
                'order_id' => $success && isset($data['data']['merchantOrderId']) ? $data['data']['merchantOrderId'] : null,
                'response' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ PhonePe notification failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send WhatsApp notification
     */
    protected function sendWhatsAppNotification($user, $amount, $debitDate)
    {
        try {
            $wpUri = "https://backend.aisensy.com/campaign/t1/api/v2";
            $userName = $user->name ?? "User";
            
            $payload = [
                "apiKey" => env('AISENSY_API_KEY', ''),
                "campaignName" => "pre_debit_notification",
                "destination" => "91" . $user->contact_no,
                "userName" => $userName,
                "templateParams" => [
                    $userName,
                    "₹" . $amount,
                    $debitDate
                ],
                "source" => "phonepe-autopay",
                "media" => [],
                "buttons" => [],
                "carouselCards" => [],
                "location" => (object)[],
                "attributes" => (object)[],
                "paramsFallbackValue" => [
                    "FirstName" => "user"
                ]
            ];
            
            $response = Http::withHeaders([
                "Accept" => "application/json",
            ])->post($wpUri, $payload);
            
            $data = $response->json();
            
            return [
                'success' => $response->successful(),
                'response' => $data
            ];
            
        } catch (\Exception $e) {
            Log::error('❌ WhatsApp notification failed', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
