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
        $config = \App\Models\Pricing\PaymentConfiguration::where('gateway', 'PhonePe')
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
    //    public function setupSubscription(Request $request)
//    {
//        $request->validate([
//            'user_id' => 'required|string',
//            'plan_id' => 'required|string',
//            'amount' => 'required|numeric|min:1',
//            'upi' => 'nullable|string',
//            'target_app' => 'nullable|string',
//        ]);
//
//        try {
//            $user = UserData::where('uid', $request->user_id)->first();
//            if (!$user) {
//                return ResponseHandler::sendResponse(
//                    $request,
//                    new ResponseInterface(404, false, 'User not found')
//                );
//            }
//
//            $amount = $request->amount;
//            $merchantOrderId = "MO_SETUP_" . uniqid() . time();
//            $merchantSubscriptionId = "MS_" . uniqid() . time();
//
//            // Create order first
//            $order = Order::create([
//                'user_id' => $request->user_id,
//                'plan_id' => $request->plan_id,
//                'crafty_id' => Order::generateCraftyId(),
//                'amount' => $amount,
//                'currency' => 'INR',
//                'status' => 'pending',
//                'type' => 'new_sub',
//                'razorpay_order_id' => 'PHONEPE_' . uniqid(), // Unique identifier for PhonePe orders
//            ]);
//
//            $token = $this->tokenService->getAccessToken();
//
//            // Determine payment mode - for sandbox, use UPI_INTENT
//            $payload = [
//                "merchantId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
//                "merchantOrderId" => $merchantOrderId,
//                "merchantUserId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
//                "amount" => $amount * 100,
//                "paymentFlow" => [
//                    "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
//                    "message" => "Monthly Subscription",
//                    "merchantUrls" => [
//                        "redirectUrl" => url('/api/phonepe/autopay/callback'),
//                        "cancelRedirectUrl" => url('/api/phonepe/autopay/callback'),
//                    ],
//                    "paymentMode" => [
//                        "type" => "UPI_INTENT",
//                        "targetApp" => $request->target_app,
//                    ],
//                    "subscriptionDetails" => [
//                        "subscriptionType" => "RECURRING",
//                        "merchantSubscriptionId" => $merchantSubscriptionId,
//                        "authWorkflowType" => "TRANSACTION",
//                        "amountType" => "FIXED",
//                        "maxAmount" => $amount * 100,
//                        "recurringAmount" => $amount * 100,
//                        "frequency" => "Monthly",
//                        "productType" => "UPI_MANDATE",
//                        "expireAt" => now()->addMonths(12)->timestamp * 1000,
//                    ],
//                ],
//                "deviceContext" => [
//                    "deviceOS"=> "ANDROID"
//                ],
//                "expireAfter" => 3000,
//            ];
//
//
//
//            // Use checkout API, not subscriptions API
//            $url = $this->production
//                ? "https://api.phonepe.com/apis/pg/checkout/v2/pay"
//                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay";
//
//            $response = Http::withHeaders([
//                "Authorization" => "O-Bearer " . $token,
//                "Content-Type" => "application/json",
//                "Accept" => "application/json"
//            ])->post($url, $payload);
//
//            $data = $response->json();
//            Log::info('📤 PhonePe AutoPay Setup Response', [
//                'merchant_order_id' => $merchantOrderId,
//                'http_code' => $response->status(),
//                'response' => $data,
//                'url' => $url
//            ]);
//
//            // Check for authorization errors
//            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
//                return ResponseHandler::sendResponse(
//                    $request,
//                    new ResponseInterface(400, false, 'PhonePe Authorization Failed', [
//                        'error' => 'Authorization failed. Please check credentials.',
//                        'details' => $data
//                    ])
//                );
//            }
//
//            // Check for successful response (checkout API returns redirectUrl)
//            if ($response->successful() && isset($data['redirectUrl'])) {
//                // Create subscription record
//                PhonePeSubscription::create([
//                    'user_id' => $request->user_id,
//                    'order_id' => $order->id,
//                    'plan_id' => $request->plan_id,
//                    'merchant_subscription_id' => $merchantSubscriptionId,
//                    'merchant_order_id' => $merchantOrderId,
//                    'phonepe_subscription_id' => $data['orderId'] ?? null,
//                    'amount' => $amount,
//                    'currency' => 'INR',
//                    'frequency' => 'Monthly',
//                    'max_amount' => $amount,
//                    'start_date' => now()->toDateString(),
//                    'next_billing_date' => now()->addMonth()->toDateString(),
//                    'status' => 'PENDING', // Will be ACTIVE after user completes payment
//                    'subscription_status' => $data['state'] ?? 'PENDING',
//                    'metadata' => [
//                        'setup_payload' => $payload,
//                        'setup_response' => $data
//                    ]
//                ]);
//
//                return ResponseHandler::sendResponse(
//                    $request,
//                    new ResponseInterface(200, true, 'Subscription setup initiated successfully', [
//                        'data' => [
//                            'merchant_order_id' => $merchantOrderId,
//                            'merchant_subscription_id' => $merchantSubscriptionId,
//                            'phonepe_order_id' => $data['orderId'] ?? null,
//                            'redirect_url' => $data['redirectUrl'],
//                            'state' => $data['state'] ?? 'PENDING',
//                            'expire_at' => $data['expireAt'] ?? null
//                        ]
//                    ])
//                );
//            }
//
//            return ResponseHandler::sendResponse(
//                $request,
//                new ResponseInterface(400, false, 'Subscription setup failed', [
//                    'error' => $data
//                ])
//            );
//
//        } catch (\Exception $e) {
//            Log::error('❌ PhonePe AutoPay Setup Exception', [
//                'error' => $e->getMessage(),
//                'trace' => $e->getTraceAsString()
//            ]);
//
//            return ResponseHandler::sendResponse(
//                $request,
//                new ResponseInterface(500, false, 'Setup failed: ' . $e->getMessage())
//            );
//        }
//    }

    public function validateUpi(Request $request)
    {
        $url = "https://api.phonepe.com/apis/pg/v2/validate/upi";
        $token = $this->tokenService->getAccessToken();
        $payload = [
            "type" => "VPA",
            "vpa" => $request->vpa,
        ];

        $response = Http::withHeaders([
            "Authorization" => "O-Bearer " . $token,
            "Content-Type" => "application/json",
            "Accept" => "application/json"
        ])->post($url, $payload);
        $data = $response->json();
        $isSuccess = $data['valid'] ?? false;

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(200, $isSuccess, $isSuccess ? 'Upi is Valid' : "Upi not valid", [
                'datas' => $data
            ])
        );
    }

    public function setupSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'upi' => 'nullable|string',
            'target_app' => 'nullable|string',
            'type' => 'nullable|numeric|min:0',
            'is_android' => 'nullable|numeric|min:0',
        ]);

        $type = $request->get('type', 0);
        $isAndroid = $request->get('is_android', 1);


        if ($type == 1 && !$request->upi) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Upi id not found')
            );
        } else {
            $targetApp = $request->get('target_app', $isAndroid ? 'com.phonepe.app' : 'PHONEPE');
        }

        try {
            $user = UserData::where('uid', $request->user_id)->first();
            if (!$user) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'User not found')
                );
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

            $paymentMode = $type == 1 ? [
                "type" => "UPI_COLLECT",
                "details" => [
                    "type" => "VPA",
                    "vpa" => $request->upi
                ],
            ] : [
                "type" => "UPI_INTENT",
                "targetApp" => $targetApp,
            ];

            $expireAt = now()->addMonths(12)->timestamp * 1000;
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => $amount * 100,
                "expireAt" => $expireAt,
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_SETUP",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => $amount * 100,
                    "frequency" => "Monthly",
                    "expireAt" => $expireAt,
                    "paymentMode" => $paymentMode,
                ],
                "deviceContext" => [
                    "deviceOS" => $isAndroid ? "ANDROID" : "iOS"
                ],
            ];
            // Use checkout API, not subscriptions API
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/setup"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/setup";

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
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'PhonePe Authorization Failed', [
                        'error' => 'Authorization failed. Please check credentials.',
                        'details' => $data
                    ])
                );
            }


            // Check for successful response (checkout API returns redirectUrl)
            if ($response->successful()) {
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

                $intentUrl = $data['intentUrl'] ?? null;
                $responseData = [
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'redirect_url' => $intentUrl,
                    'state' => $data['state'] ?? 'PENDING',
                    'expire_at' => $data['expireAt'] ?? null
                ];

                // Check if showDecoded parameter is present
                $showDecoded = $request->get('showDecoded', false);
                if ($showDecoded && $intentUrl) {
                    $decodedParams = $this->parseUpiIntentUrl($intentUrl);
                    $qrCodeData = $this->generateQRCodeData($intentUrl);

                    $responseData['decoded_params'] = $decodedParams;
                    $responseData['qr_code'] = $qrCodeData;
                }

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Subscription setup initiated successfully', [
                        'data' => $responseData
                    ])
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(400, false, 'Subscription setup failed', [
                    'error' => $data
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ PhonePe AutoPay Setup Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Setup failed: ' . $e->getMessage())
            );
        }
    }

    /**
     * Setup UPI AutoPay subscription with QR Code generation
     * GET /api/phonepe/autopay/setup?showDecoded=1
     *
     * This endpoint:
     * 1. Creates subscription setup
     * 2. Parses the UPI intent URL
     * 3. Generates QR code from the intent URL
     * 4. Returns both decoded parameters and QR code
     */
    public function setupSubscriptionWithQR(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'upi' => 'nullable|string',
            'target_app' => 'nullable|string',
            'type' => 'nullable|numeric|min:0',
            'is_android' => 'nullable|numeric|min:0',
            'showDecoded' => 'nullable|boolean',
        ]);

        $showDecoded = $request->get('showDecoded', false);
        $type = $request->get('type', 0);
        $isAndroid = $request->get('is_android', 1);

        if ($type == 1 && !$request->upi) {
            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Upi id not found')
            );
        } else {
            $targetApp = $request->get('target_app', $isAndroid ? 'com.phonepe.app' : 'PHONEPE');
        }

        try {
            $user = UserData::where('uid', $request->user_id)->first();
            if (!$user) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'User not found')
                );
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
                'razorpay_order_id' => 'PHONEPE_' . uniqid(),
            ]);

            $token = $this->tokenService->getAccessToken();

            $paymentMode = $type == 1 ? [
                "type" => "UPI_COLLECT",
                "details" => [
                    "type" => "VPA",
                    "vpa" => $request->upi
                ],
            ] : [
                "type" => "UPI_INTENT",
                "targetApp" => $targetApp,
            ];

            $expireAt = now()->addMonths(12)->timestamp * 1000;
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => $amount * 100,
                "expireAt" => $expireAt,
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_SETUP",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => $amount * 100,
                    "frequency" => "Monthly",
                    "expireAt" => $expireAt,
                    "paymentMode" => $paymentMode,
                ],
                "deviceContext" => [
                    "deviceOS" => $isAndroid ? "ANDROID" : "iOS"
                ],
            ];

            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/setup"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/setup";

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

            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'PhonePe Authorization Failed', [
                        'error' => 'Authorization failed. Please check credentials.',
                        'details' => $data
                    ])
                );
            }

            if ($response->successful()) {
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
                    'status' => 'PENDING',
                    'subscription_status' => $data['state'] ?? 'PENDING',
                    'metadata' => [
                        'setup_payload' => $payload,
                        'setup_response' => $data
                    ]
                ]);

                $intentUrl = $data['intentUrl'] ?? null;
                $responseData = [
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'redirect_url' => $intentUrl,
                    'state' => $data['state'] ?? 'PENDING',
                    'expire_at' => $data['expireAt'] ?? null
                ];

                // If showDecoded is true, parse the UPI intent URL and generate QR code
                if ($showDecoded && $intentUrl) {
                    $decodedParams = $this->parseUpiIntentUrl($intentUrl);
                    $qrCodeData = $this->generateQRCodeData($intentUrl);

                    $responseData['decoded_params'] = $decodedParams;
                    $responseData['qr_code'] = $qrCodeData;
                }

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Subscription setup initiated successfully', [
                        'data' => $responseData
                    ])
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(400, false, 'Subscription setup failed', [
                    'error' => $data
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ PhonePe AutoPay Setup Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Setup failed: ' . $e->getMessage())
            );
        }
    }

    /**
     * Parse UPI intent URL and extract parameters
     */
    private function parseUpiIntentUrl($intentUrl)
    {
        if (!$intentUrl || !str_starts_with($intentUrl, 'upi://')) {
            return null;
        }

        // Extract query string from UPI URL
        $parts = parse_url($intentUrl);
        $queryString = $parts['query'] ?? '';

        parse_str($queryString, $params);

        return [
            'merchant_name' => $params['mn'] ?? null,
            'version' => $params['ver'] ?? null,
            'revocable' => $params['rev'] ?? null,
            'purpose' => $params['purpose'] ?? null,
            'validity_end' => $params['validityend'] ?? null,
            'qr_timestamp' => $params['QRts'] ?? null,
            'qr_expire' => $params['QRexpire'] ?? null,
            'transaction_type' => $params['txnType'] ?? null,
            'amount' => $params['am'] ?? null,
            'validity_start' => $params['validitystart'] ?? null,
            'mode' => $params['mode'] ?? null,
            'payee_address' => $params['pa'] ?? null,
            'currency' => $params['cu'] ?? null,
            'amount_rule' => $params['amrule'] ?? null,
            'first_amount' => $params['fam'] ?? null,
            'merchant_code' => $params['mc'] ?? null,
            'qr_medium' => $params['qrMedium'] ?? null,
            'recurrence' => $params['recur'] ?? null,
            'merchant_genre' => $params['mg'] ?? null,
            'share' => $params['share'] ?? null,
            'block' => $params['block'] ?? null,
            'transaction_ref' => $params['tr'] ?? null,
            'payee_name' => $params['pn'] ?? null,
        ];
    }

    /**
     * Generate QR code data from UPI intent URL
     */
    private function generateQRCodeData($intentUrl)
    {
        try {
            // Return the raw URL for React to generate QR code
            // React can use libraries like 'qrcode.react' or 'react-qr-code'
            return [
                'url_for_qr' => $intentUrl,
                'raw_upi_string' => $intentUrl,
                'instructions' => [
                    'en' => 'Scan this QR code with any UPI app to set up AutoPay mandate',
                    'hi' => 'AutoPay mandate सेट करने के लिए किसी भी UPI ऐप से इस QR कोड को स्कैन करें',
                    'gu' => 'AutoPay mandate સેટ કરવા માટે કોઈપણ UPI એપ્લિકેશન વડે આ QR કોડ સ્કેન કરો'
                ],
                'note' => 'Use this URL in React QR code library like qrcode.react or react-qr-code'
            ];
        } catch (\Exception $e) {
            Log::error('QR Code data preparation failed', ['error' => $e->getMessage()]);
            return [
                'url_for_qr' => $intentUrl,
                'raw_upi_string' => $intentUrl,
                'error' => 'QR code data preparation failed'
            ];
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
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'Subscription not found')
                );
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

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Subscription cancelled successfully')
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface($response->status(), false, 'Cancellation failed', [
                    'error' => $response->json()
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ Subscription cancellation exception', [
                'error' => $e->getMessage()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Cancellation failed: ' . $e->getMessage())
            );
        }
    }

    /**
     * Get subscription status
     * GET /api/phonepe/autopay/status/{merchantSubscriptionId}
     */
    public function getSubscriptionStatus(Request $request, $merchantSubscriptionId)
    {
        try {
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $merchantSubscriptionId)->first();

            if (!$subscription) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'Subscription not found')
                );
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

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Subscription status retrieved', [
                        'data' => [
                            'local_status' => $subscription->status,
                            'phonepe_status' => $data['state'] ?? null,
                            'details' => $data
                        ]
                    ])
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface($response->status(), false, 'Status check failed', [
                    'error' => $data
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ Status check exception', [
                'error' => $e->getMessage()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Status check failed: ' . $e->getMessage())
            );
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
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(404, false, 'Active subscription not found')
                );
            }

            // Check if already processed today
            $today = now()->toDateString();
            $alreadyProcessed = PhonePeAutoPayTransaction::where('subscription_id', $subscription->id)
                ->where('transaction_type', 'manual')
                ->whereDate('created_at', $today)
                ->exists();

            if ($alreadyProcessed) {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'Manual redemption already triggered today')
                );
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
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'PhonePe Subscription Redemption API Not Available in Sandbox', [
                        'error' => 'The subscription redemption/auto-debit API is only available in production environment. In sandbox, you can only test subscription setup.',
                        'details' => [
                            'subscription_id' => $subscription->id,
                            'merchant_subscription_id' => $request->merchant_subscription_id,
                            'amount' => $subscription->amount,
                            'next_billing_date' => $subscription->next_billing_date,
                            'http_code' => 204,
                            'note' => 'To test auto-debit, you need production credentials and a live UPI mandate.'
                        ]
                    ])
                );
            }

            // Check for authorization errors (sandbox limitation)
            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'PhonePe Subscription Redemption API Not Available in Sandbox', [
                        'error' => 'The subscription redemption/auto-debit API is only available in production environment. In sandbox, you can only test subscription setup.',
                        'details' => [
                            'subscription_id' => $subscription->id,
                            'merchant_subscription_id' => $request->merchant_subscription_id,
                            'amount' => $subscription->amount,
                            'next_billing_date' => $subscription->next_billing_date,
                            'note' => 'To test auto-debit, you need production credentials and a live UPI mandate.'
                        ]
                    ])
                );
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

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Manual redemption triggered', [
                        'data' => [
                            'merchant_order_id' => $merchantOrderId,
                            'phonepe_order_id' => $data['orderId']
                        ]
                    ])
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface($response->status(), false, 'Manual redemption failed', [
                    'error' => $data
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ Manual redemption exception', [
                'error' => $e->getMessage()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'Manual redemption failed: ' . $e->getMessage())
            );
        }
    }

    /**
     * Generate QR Code for AutoPay Subscription
     * POST /api/phonepe/autopay/generate-qr
     * 
     * This API creates a subscription and returns QR code data for React frontend
     */
    public function generateQRCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'upi' => 'nullable|string',
            'target_app' => 'nullable|string',
        ]);

        try {
            // Generate unique IDs
            $merchantOrderId = 'MO_QR_' . strtoupper(Str::random(15)) . time();
            $merchantSubscriptionId = 'MS_QR_' . strtoupper(Str::random(15)) . time();

            // Get OAuth token
            $token = $this->tokenService->getAccessToken();

            // Prepare subscription payload using CHECKOUT API (not subscription API)
            $payload = [
                "merchantId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
                "merchantOrderId" => $merchantOrderId,
                "merchantUserId" => env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC'),
                "amount" => (int) ($request->amount * 100),
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
                    "message" => "Subscription Payment",
                    "merchantUrls" => [
                        "redirectUrl" => url('/api/phonepe/autopay/callback'),
                        "cancelRedirectUrl" => url('/api/phonepe/autopay/callback'),
                    ],
                    "paymentMode" => [
                        "type" => "UPI_INTENT",
                        "targetApp" => $request->target_app ?? "com.phonepe.app",
                    ],
                    "subscriptionDetails" => [
                        "subscriptionType" => "RECURRING",
                        "merchantSubscriptionId" => $merchantSubscriptionId,
                        "authWorkflowType" => "TRANSACTION",
                        "amountType" => "FIXED",
                        "maxAmount" => (int) ($request->amount * 100),
                        "recurringAmount" => (int) ($request->amount * 100),
                        "frequency" => "Monthly",
                        "productType" => "UPI_MANDATE",
                        "expireAt" => now()->addMonths(12)->timestamp * 1000,
                    ],
                ],
                "deviceContext" => [
                    "deviceOS" => "ANDROID"
                ],
                "expireAfter" => 3000,
            ];

            // Add UPI if provided
            if ($request->upi) {
                $payload['paymentFlow']['paymentMode']['vpa'] = $request->upi;
            }

            // Use CHECKOUT API (not subscription API) - this works in sandbox
            $url = $this->production
                ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

            Log::info('🔄 Creating subscription for QR code', [
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'amount' => $request->amount,
                'environment' => $this->production ? 'production' : 'sandbox',
                'url' => $url
            ]);

            // Make API request
            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->post($url, $payload);

            $data = $response->json();

            Log::info('📤 PhonePe QR Code Response', [
                'http_code' => $response->status(),
                'response' => $data
            ]);

            // Check for authorization errors
            if (isset($data['code']) && $data['code'] === 'AUTHORIZATION_FAILED') {
                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(400, false, 'PhonePe Authorization Failed', [
                        'error' => 'Authorization failed. Please check credentials.',
                        'details' => $data
                    ])
                );
            }

            // Check for successful response (checkout API returns redirectUrl)
            if ($response->successful() && isset($data['redirectUrl'])) {
                // Create Order record
                Order::create([
                    'user_id' => $request->user_id,
                    'plan_id' => $request->plan_id,
                    'crafty_id' => Order::generateCraftyId(),
                    'amount' => $request->amount,
                    'currency' => 'INR',
                    'status' => 'pending',
                    'type' => 'new_sub',
                    'razorpay_order_id' => 'PHONEPE_QR_' . uniqid(),
                ]);

                // Create Subscription record
                PhonePeSubscription::create([
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'merchant_order_id' => $merchantOrderId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'phonepe_subscription_id' => $data['orderId'] ?? null,
                    'user_id' => $request->user_id,
                    'plan_id' => $request->plan_id,
                    'amount' => $request->amount,
                    'currency' => 'INR',
                    'frequency' => 'Monthly',
                    'max_amount' => $request->amount,
                    'start_date' => now()->toDateString(),
                    'next_billing_date' => now()->addMonth()->toDateString(),
                    'status' => 'PENDING',
                    'subscription_status' => $data['state'] ?? 'PENDING',
                    'metadata' => [
                        'setup_payload' => $payload,
                        'setup_response' => $data
                    ]
                ]);

                $redirectUrl = $data['redirectUrl'] ?? null;

                if (!$redirectUrl) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(400, false, 'Redirect URL not received from PhonePe')
                    );
                }

                // Parse UPI intent URL from redirect URL
                // The redirectUrl contains the UPI intent URL
                $intentUrl = $redirectUrl;

                // Generate QR code
                $qrCodeBase64 = $this->generateQRCodeData($intentUrl);

                // Parse UPI parameters
                $decodedParams = $this->parseUpiIntentUrl($intentUrl);

                Log::info('✅ QR Code generated successfully', [
                    'merchant_subscription_id' => $merchantSubscriptionId
                ]);

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'QR Code generated successfully', [
                        'merchant_order_id' => $merchantOrderId,
                        'merchant_subscription_id' => $merchantSubscriptionId,
                        'phonepe_order_id' => $data['orderId'] ?? null,
                        'state' => $data['state'] ?? 'PENDING',
                        'expire_at' => $data['expireAt'] ?? null,
                        'qr_code' => [
                            'base64' => $qrCodeBase64,
                            'redirect_url' => $redirectUrl,
                            'intent_url' => $intentUrl,
                            'decoded_params' => $decodedParams
                        ],
                        'instructions' => [
                            'step_1' => 'Open any UPI app (PhonePe, GPay, Paytm, etc.)',
                            'step_2' => 'Tap on "Scan QR Code" option',
                            'step_3' => 'Scan this QR code with your phone camera',
                            'step_4' => 'Verify amount and complete payment'
                        ]
                    ])
                );
            }

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(400, false, 'QR Code generation failed', [
                    'error' => $data,
                    'message' => $data['message'] ?? 'Unknown error'
                ])
            );

        } catch (\Exception $e) {
            Log::error('❌ QR Code generation exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return ResponseHandler::sendResponse(
                $request,
                new ResponseInterface(500, false, 'QR Code generation failed: ' . $e->getMessage())
            );
        }
    }
}

