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

                // The intentUrl from PhonePe response is already in UPI format (upi://mandate?...)
                // This is the URL that should be used for QR code generation
                $upiUrl = $intentUrl;

                $responseData = [
                    'merchant_order_id' => $merchantOrderId,
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_order_id' => $data['orderId'] ?? null,
                    'redirect_url' => $intentUrl, // This is the UPI URL for QR code
                    'state' => $data['state'] ?? 'PENDING',
                    'expire_at' => $data['expireAt'] ?? null
                ];

                // If showDecoded is true, parse the UPI intent URL and generate QR code
                if ($showDecoded && $upiUrl) {
                    $decodedParams = $this->parseUpiIntentUrl($upiUrl);
                    $qrCodeData = $this->generateQRCodeData($upiUrl);

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
    private function generateQRCodeData($upiUrl)
    {
        try {
            // Return the UPI URL for QR code generation
            // The UPI URL (upi://mandate?...) should be used directly in QR code libraries
            // React can use libraries like 'qrcode.react' or 'react-qr-code'
            return [
                'url_for_qr' => $upiUrl,
                'raw_upi_string' => $upiUrl,
                'instructions' => [
                    'en' => 'Scan this QR code with any UPI app to set up AutoPay mandate',
                    'hi' => 'AutoPay mandate सेट करने के लिए किसी भी UPI ऐप से इस QR कोड को स्कैन करें',
                    'gu' => 'AutoPay mandate સેટ કરવા માટે કોઈપણ UPI એપ્લિકેશન વડે આ QR કોડ સ્કેન કરો'
                ],
                'note' => 'Use this UPI URL (upi://mandate?...) in React QR code library like qrcode.react or react-qr-code'
            ];
        } catch (\Exception $e) {
            Log::error('QR Code data preparation failed', ['error' => $e->getMessage()]);
            return [
                'url_for_qr' => $upiUrl,
                'raw_upi_string' => $upiUrl,
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
                // Map PhonePe status to our local status
                $phonepeState = $data['state'] ?? 'UNKNOWN';

                // Update subscription status based on PhonePe response
                $subscription->subscription_status = $phonepeState;

                // Map to local status
                $localStatus = $this->mapPhonePeStatusToLocal($phonepeState);
                $subscription->status = $localStatus;
                $subscription->save();

                return ResponseHandler::sendResponse(
                    $request,
                    new ResponseInterface(200, true, 'Subscription status retrieved', [
                        'data' => [
                            'state' => $localStatus,
                            'subscription_id' => $data['subscriptionId'] ?? null,
                            'merchant_subscription_id' => $merchantSubscriptionId,
                            'is_active' => in_array($localStatus, ['ACTIVE', 'COMPLETED']),
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
     * Map PhonePe status to local status
     */
    private function mapPhonePeStatusToLocal($phonepeState)
    {
        $statusMap = [
            'PENDING' => 'PENDING',
            'ACTIVATION_IN_PROGRESS' => 'PENDING',
            'ACTIVE' => 'ACTIVE',
            'COMPLETED' => 'COMPLETED',
            'FAILED' => 'FAILED',
            'CANCELLED' => 'CANCELLED',
            'EXPIRED' => 'EXPIRED',
        ];

        return $statusMap[$phonepeState] ?? 'UNKNOWN';
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
     * This API creates a subscription and constructs UPI mandate URL for QR code
     */
    public function generateQRCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'plan_id' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            // Generate unique IDs
            $merchantOrderId = 'MO_QR_' . strtoupper(Str::random(15)) . time();
            $merchantSubscriptionId = 'MS_QR_' . strtoupper(Str::random(15)) . time();

            // Get OAuth token
            $token = $this->tokenService->getAccessToken();

            $amount = $request->amount;
            $expireAt = now()->addMonths(12)->timestamp * 1000;

            // Use Subscription API v2 - it will return intentUrl which we'll extract UPI from
            $payload = [
                "merchantOrderId" => $merchantOrderId,
                "amount" => (int) ($amount * 100),
                "expireAt" => $expireAt,
                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_SETUP",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => (int) ($amount * 100),
                    "frequency" => "Monthly",
                    "expireAt" => $expireAt,
                    "paymentMode" => [
                        "type" => "UPI_INTENT",
                        "targetApp" => "com.phonepe.app",
                    ],
                ],
                "deviceContext" => [
                    "deviceOS" => "ANDROID"
                ],
            ];

            // Use Subscription API v2
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/setup"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/setup";

            Log::info('🔄 Creating subscription for QR code', [
                'merchant_order_id' => $merchantOrderId,
                'merchant_subscription_id' => $merchantSubscriptionId,
                'amount' => $amount,
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

            // Check for successful response
            if ($response->successful()) {
                // Create Order record
                $order = Order::create([
                    'user_id' => $request->user_id,
                    'plan_id' => $request->plan_id,
                    'crafty_id' => Order::generateCraftyId(),
                    'amount' => $amount,
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
                    'order_id' => $order->id,
                    'plan_id' => $request->plan_id,
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

                // Get the intent URL from PhonePe response
                $intentUrl = $data['intentUrl'] ?? null;

                if (!$intentUrl) {
                    return ResponseHandler::sendResponse(
                        $request,
                        new ResponseInterface(400, false, 'Intent URL not received from PhonePe', [
                            'response' => $data
                        ])
                    );
                }

                // Check if intentUrl is already in UPI format
                $upiUrl = $intentUrl;
                $isUpiFormat = strpos($intentUrl, 'upi://') === 0;

                // If it's HTTPS URL, we need to construct UPI mandate URL manually
                if (!$isUpiFormat) {
                    // Construct UPI mandate URL from subscription details
                    $merchantId = env('PHONEPE_MERCHANT_ID', 'M23LAMPVYPELC');
                    $validityStart = now()->format('dmY');
                    $validityEnd = now()->addMonths(12)->format('dmY');
                    $qrExpire = now()->addMinutes(15)->format('dmY');

                    // Build UPI mandate URL
                    $upiUrl = "upi://mandate?" . http_build_query([
                        'pa' => $merchantId . '@ybl',
                        'pn' => 'craftyartapp.com',
                        'tr' => $data['orderId'] ?? $merchantOrderId,
                        'cu' => 'INR',
                        'am' => number_format($amount, 2, '.', ''),
                        'fam' => number_format($amount, 2, '.', ''),
                        'mc' => '4816',
                        'mode' => '04',
                        'purpose' => '14',
                        'rev' => 'Y',
                        'share' => 'Y',
                        'block' => 'N',
                        'txnType' => 'CREATE',
                        'validitystart' => $validityStart,
                        'validityend' => $validityEnd,
                        'amrule' => 'MAX',
                        'recur' => 'ASPRESENTED',
                        'recurringAmount' => number_format($amount, 2, '.', ''),
                        'recurringType' => 'MONTHLY',
                        'mn' => 'Autopay',
                        'ver' => '01',
                        'QRts' => now()->toIso8601String(),
                        'QRexpire' => now()->addMinutes(15)->toIso8601String(),
                        'qrMedium' => '00',
                        'mg' => 'ONLINE',
                    ]);
                }

                // Generate QR code data
                $qrCodeData = $this->generateQRCodeData($upiUrl);

                // Parse UPI parameters
                $decodedParams = $this->parseUpiIntentUrl($upiUrl);

                Log::info('✅ QR Code generated successfully', [
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'original_url' => $intentUrl,
                    'upi_url' => $upiUrl,
                    'is_upi_format' => strpos($upiUrl, 'upi://') === 0
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
                            'base64' => $qrCodeData,
                            'intent_url' => $upiUrl,
                            'original_url' => $intentUrl,
                            'decoded_params' => $decodedParams
                        ],
                        'instructions' => [
                            'step_1' => 'Open any UPI app (PhonePe, GPay, Paytm, etc.)',
                            'step_2' => 'Tap on "Scan QR Code" option',
                            'step_3' => 'Scan this QR code with your phone camera',
                            'step_4' => 'Verify amount and complete mandate setup'
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

    /**
     * Handle PhonePe Webhook for automatic status updates
     * POST /api/phonepe/autopay/webhook
     * 
     * PhonePe sends webhook when:
     * - User approves mandate (ACTIVE)
     * - User declines mandate (FAILED)
     * - Payment succeeds (COMPLETED)
     * - Payment fails (PAYMENT_FAILED)
     * - Subscription cancelled (CANCELLED)
     */
    public function handleWebhook(Request $request)
    {
        try {
            $payload = $request->all();

            Log::info('📥 PhonePe Webhook Received', [
                'payload' => $payload,
                'headers' => $request->headers->all()
            ]);

            // Extract subscription ID from webhook
            $merchantSubscriptionId = $payload['merchantSubscriptionId'] ?? null;
            $phonepeSubscriptionId = $payload['subscriptionId'] ?? null;
            $state = $payload['state'] ?? null;
            $transactionId = $payload['transactionId'] ?? null;

            if (!$merchantSubscriptionId && !$phonepeSubscriptionId) {
                Log::warning('⚠️ Webhook missing subscription ID');
                return response()->json(['success' => false, 'message' => 'Missing subscription ID'], 400);
            }

            // Find subscription
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $merchantSubscriptionId)
                ->orWhere('phonepe_subscription_id', $phonepeSubscriptionId)
                ->first();

            if (!$subscription) {
                Log::warning('⚠️ Subscription not found in webhook', [
                    'merchant_subscription_id' => $merchantSubscriptionId,
                    'phonepe_subscription_id' => $phonepeSubscriptionId
                ]);
                return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
            }

            // Map PhonePe status to local status
            $localStatus = $this->mapPhonePeStatusToLocal($state);

            // Update subscription status
            $subscription->subscription_status = $state;
            $subscription->status = $localStatus;

            // Update metadata with webhook data
            $metadata = $subscription->metadata ?? [];
            $metadata['webhooks'] = $metadata['webhooks'] ?? [];
            $metadata['webhooks'][] = [
                'received_at' => now()->toIso8601String(),
                'state' => $state,
                'transaction_id' => $transactionId,
                'payload' => $payload
            ];
            $subscription->metadata = $metadata;
            $subscription->save();

            // Update related order status if exists
            if ($subscription->order_id) {
                $order = Order::find($subscription->order_id);
                if ($order) {
                    if (in_array($localStatus, ['ACTIVE', 'COMPLETED'])) {
                        $order->status = 'completed';
                    } elseif ($localStatus === 'FAILED') {
                        $order->status = 'failed';
                    }
                    $order->save();

                    Log::info('✅ Order status updated', [
                        'order_id' => $order->id,
                        'new_status' => $order->status
                    ]);
                }
            }

            Log::info('✅ Webhook processed successfully', [
                'subscription_id' => $subscription->id,
                'old_status' => $subscription->getOriginal('status'),
                'new_status' => $localStatus,
                'phonepe_state' => $state
            ]);

            // Return success response to PhonePe
            return response()->json([
                'success' => true,
                'message' => 'Webhook processed successfully'
            ], 200);

        } catch (\Exception $e) {
            Log::error('❌ Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Still return 200 to PhonePe to avoid retries
            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 200);
        }
    }

    /**
     * Manually sync subscription status from PhonePe
     * Useful when webhook is missed or for testing
     */
    public function syncSubscriptionStatus($merchantSubscriptionId)
    {
        try {
            $subscription = PhonePeSubscription::where('merchant_subscription_id', $merchantSubscriptionId)->first();

            if (!$subscription) {
                return [
                    'success' => false,
                    'message' => 'Subscription not found'
                ];
            }

            // Get latest status from PhonePe
            $token = $this->tokenService->getAccessToken();
            $url = $this->production
                ? "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";

            $response = Http::withHeaders([
                "Authorization" => "O-Bearer " . $token,
                "Content-Type" => "application/json",
                "Accept" => "application/json"
            ])->get($url);

            if ($response->successful()) {
                $data = $response->json();
                $phonepeState = $data['state'] ?? 'UNKNOWN';
                $localStatus = $this->mapPhonePeStatusToLocal($phonepeState);

                // Update subscription
                $oldStatus = $subscription->status;
                $subscription->subscription_status = $phonepeState;
                $subscription->status = $localStatus;
                $subscription->save();

                // Update order if status changed
                if ($oldStatus !== $localStatus && $subscription->order_id) {
                    $order = Order::find($subscription->order_id);
                    if ($order) {
                        if (in_array($localStatus, ['ACTIVE', 'COMPLETED'])) {
                            $order->status = 'completed';
                        } elseif ($localStatus === 'FAILED') {
                            $order->status = 'failed';
                        }
                        $order->save();
                    }
                }

                Log::info('✅ Subscription synced', [
                    'subscription_id' => $subscription->id,
                    'old_status' => $oldStatus,
                    'new_status' => $localStatus
                ]);

                return [
                    'success' => true,
                    'message' => 'Status synced successfully',
                    'old_status' => $oldStatus,
                    'new_status' => $localStatus,
                    'phonepe_state' => $phonepeState
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch status from PhonePe'
            ];

        } catch (\Exception $e) {
            Log::error('❌ Sync failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ];
        }
    }
}

