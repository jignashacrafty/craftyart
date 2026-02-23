<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pricing\PaymentConfiguration;
use App\Models\Sale;
use App\Services\PhonePeTokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SimplePaymentController extends Controller
{
    public function createPaymentLink(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'email' => 'nullable|email',
                'contact_no' => 'nullable|string|max:15',
                'user_name' => 'nullable|string|max:255',
                'payment_method' => 'nullable|in:razorpay,phonepe',
                'description' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $amount = $request->amount;
            $email = $request->email ?? 'customer@craftyartapp.com';
            $contactNo = $request->contact_no ?? '9999999999';
            $userName = $request->user_name ?? 'Customer';
            $paymentMethod = $request->payment_method ?? 'phonepe';
            $description = $request->description ?? 'Payment for services';

            $referenceId = $this->generateReferenceId();

            $sale = Sale::create([
                'sales_person_id' => null,
                'user_name' => $userName,
                'email' => $email,
                'contact_no' => $contactNo,
                'payment_method' => $paymentMethod,
                'plan_id' => 'API_PAYMENT',
                'subscription_type' => 'custom',
                'amount' => $amount,
                'plan_type' => 'custom',
                'reference_id' => $referenceId,
                'status' => 'created',
                'usage_type' => 'custom',
            ]);

            Log::info('Simple Payment Link Creation Started', [
                'reference_id' => $referenceId,
                'amount' => $amount,
                'payment_method' => $paymentMethod
            ]);

            $paymentLink = null;
            
            if ($paymentMethod === 'razorpay') {
                $paymentLink = $this->createRazorpayPaymentLink($sale, $description);
            } elseif ($paymentMethod === 'phonepe') {
                $paymentLink = $this->createPhonePePaymentLink($sale, $description);
            }

            if (!$paymentLink) {
                throw new \Exception('Failed to create payment link');
            }

            $sale->update([
                'payment_link_id' => $paymentLink['id'],
                'payment_link_url' => $paymentLink['payment_link_url'],
                'short_url' => $paymentLink['short_url'] ?? $paymentLink['payment_link_url'],
                'phonepe_order_id' => $paymentLink['phonepe_order_id'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment link created successfully',
                'data' => [
                    'reference_id' => $sale->reference_id,
                    'payment_link' => $sale->short_url ?? $sale->payment_link_url,
                    'amount' => $sale->amount,
                    'payment_method' => $sale->payment_method,
                    'status' => $sale->status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Simple Payment Link Creation Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creating payment link: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkPaymentStatus(Request $request)
    {
        try {
            $referenceId = $request->reference_id;

            if (!$referenceId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference ID is required'
                ], 400);
            }

            $sale = Sale::where('reference_id', $referenceId)->first();

            if (!$sale) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'reference_id' => $sale->reference_id,
                    'amount' => $sale->amount,
                    'status' => $sale->status,
                    'payment_method' => $sale->payment_method,
                    'paid_at' => $sale->paid_at,
                    'order_id' => $sale->order_id,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Check Payment Status Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking payment status: ' . $e->getMessage()
            ], 500);
        }
    }

    private function generateReferenceId()
    {
        do {
            $referenceId = 'REF_' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 12));
        } while (Sale::where('reference_id', $referenceId)->exists());

        return $referenceId;
    }

    private function getPaymentCredentials($gateway, $scope = 'NATIONAL')
    {
        $config = PaymentConfiguration::whereRaw('LOWER(gateway) = ?', [strtolower($gateway)])
            ->where('payment_scope', $scope)
            ->where('is_active', 1)
            ->first();

        if (!$config) {
            Log::warning("Payment configuration not found", [
                'gateway' => $gateway,
                'scope' => $scope
            ]);
            return null;
        }

        return $config->credentials;
    }

    private function createRazorpayPaymentLink($sale, $description)
    {
        try {
            $credentials = $this->getPaymentCredentials('razorpay', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('Razorpay credentials not configured');
            }

            $razorpayKey = $credentials['key_id'] ?? null;
            $razorpaySecret = $credentials['secret_key'] ?? $credentials['key_secret'] ?? null;

            if (!$razorpayKey || !$razorpaySecret) {
                throw new \Exception('Razorpay credentials incomplete');
            }

            $url = 'https://api.razorpay.com/v1/payment_links';

            $data = [
                'amount' => $sale->amount * 100,
                'currency' => 'INR',
                'accept_partial' => false,
                'description' => $description,
                'customer' => [
                    'name' => $sale->user_name,
                    'email' => $sale->email,
                    'contact' => $sale->contact_no,
                ],
                'notify' => [
                    'sms' => true,
                    'email' => true,
                ],
                'reminder_enable' => true,
                'reference_id' => $sale->reference_id,
                'callback_url' => url('/api/payment/razorpay-webhook'),
                'callback_method' => 'get',
            ];

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, $razorpayKey . ':' . $razorpaySecret);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $result = json_decode($response, true);
                return [
                    'id' => $result['id'],
                    'payment_link_url' => $result['short_url'] ?? $result['url'] ?? null,
                    'short_url' => $result['short_url'] ?? null,
                ];
            } else {
                $errorResponse = json_decode($response, true);
                $errorMessage = $errorResponse['error']['description'] ?? 'Razorpay API Error';
                
                Log::error('Razorpay API Error', [
                    'http_code' => $httpCode,
                    'response' => $response
                ]);
                
                throw new \Exception($errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Razorpay Payment Link Creation Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function createPhonePePaymentLink($sale, $description)
    {
        try {
            $credentials = $this->getPaymentCredentials('phonepe', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured');
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $environment = $credentials['environment'] ?? 'sandbox';

            if (!$merchantId) {
                throw new \Exception('PhonePe merchant_id is required');
            }

            $tokenService = app(PhonePeTokenService::class);
            $token = $tokenService->getAccessToken();

            if (!$token) {
                throw new \Exception('Failed to get PhonePe access token');
            }

            $merchantOrderId = 'TX' . time() . rand(100000, 999999);

            $payload = [
                'merchantId' => $merchantId,
                'merchantOrderId' => $merchantOrderId,
                'merchantUserId' => $merchantId,
                'amount' => (int)($sale->amount * 100),
                'paymentFlow' => [
                    'type' => 'PG_CHECKOUT',
                    'message' => $description,
                    'merchantUrls' => [
                        'redirectUrl' => url('/api/payment/phonepe-webhook?ref=' . $sale->reference_id)
                    ]
                ]
            ];

            $apiUrl = ($environment === 'production')
                ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
                : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

            Log::info('PhonePe Payment Request', [
                'reference_id' => $sale->reference_id,
                'merchant_order_id' => $merchantOrderId,
                'amount' => $sale->amount,
                'environment' => $environment
            ]);

            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($apiUrl, $payload);

            $httpCode = $response->status();
            $responseData = $response->json();

            Log::info('PhonePe API Response', [
                'http_code' => $httpCode,
                'response' => $responseData
            ]);

            if ($httpCode === 200 && isset($responseData['redirectUrl'])) {
                $phonePeOrderId = $responseData['orderId'] ?? $merchantOrderId;
                $paymentUrl = $responseData['redirectUrl'];

                return [
                    'id' => $merchantOrderId,
                    'phonepe_order_id' => $phonePeOrderId,
                    'payment_link_url' => $paymentUrl,
                    'short_url' => $paymentUrl,
                ];
            } else {
                $errorMessage = $responseData['message'] ?? $responseData['error'] ?? 'PhonePe API error';
                $errorCode = $responseData['code'] ?? $responseData['errorCode'] ?? 'UNKNOWN';

                Log::error('PhonePe API Error', [
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'full_response' => $responseData
                ]);

                throw new \Exception("PhonePe Error [{$errorCode}]: {$errorMessage}");
            }

        } catch (\Exception $e) {
            Log::error('PhonePe Payment Link Creation Error', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function razorpayWebhook(Request $request)
    {
        try {
            Log::info('Razorpay Webhook Received', [
                'all_params' => $request->all()
            ]);

            $referenceId = $request->get('reference_id') 
                        ?? $request->get('razorpay_payment_link_reference_id')
                        ?? null;

            $paymentId = $request->get('razorpay_payment_id');
            $paymentLinkId = $request->get('razorpay_payment_link_id');
            $paymentLinkStatus = $request->get('razorpay_payment_link_status');

            if (!$referenceId && $paymentLinkId) {
                $sale = Sale::where('payment_link_id', $paymentLinkId)->first();
                if ($sale) {
                    $referenceId = $sale->reference_id;
                }
            }

            if (!$referenceId) {
                Log::error('No reference_id found in Razorpay webhook');
                return response()->json(['success' => false, 'message' => 'No reference ID'], 400);
            }

            $sale = Sale::where('reference_id', $referenceId)->first();

            if (!$sale) {
                Log::error('Sale not found for reference_id', ['reference_id' => $referenceId]);
                return response()->json(['success' => false, 'message' => 'Sale not found'], 404);
            }

            if ($paymentLinkStatus === 'paid') {
                $sale->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('Payment successful via Razorpay webhook', [
                    'reference_id' => $referenceId,
                    'payment_id' => $paymentId
                ]);

                return redirect()->to(url('/payment-success?ref=' . $referenceId));
            } else {
                Log::warning('Payment not successful', [
                    'reference_id' => $referenceId,
                    'status' => $paymentLinkStatus
                ]);

                return redirect()->to(url('/payment-failed?ref=' . $referenceId));
            }

        } catch (\Exception $e) {
            Log::error('Razorpay Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing error'
            ], 500);
        }
    }

    public function phonePeWebhook(Request $request)
    {
        try {
            Log::info('PhonePe Webhook Received', [
                'all_params' => $request->all()
            ]);

            $referenceId = $request->get('ref');

            if (!$referenceId) {
                Log::error('No reference_id found in PhonePe webhook');
                return redirect()->to(url('/payment-failed?error=no_reference'));
            }

            $sale = Sale::where('reference_id', $referenceId)->first();

            if (!$sale) {
                Log::error('Sale not found for reference_id', ['reference_id' => $referenceId]);
                return redirect()->to(url('/payment-failed?ref=' . $referenceId . '&error=sale_not_found'));
            }

            if ($sale->status === 'paid') {
                Log::info('Payment already processed', ['reference_id' => $referenceId]);
                return redirect()->to(url('/payment-success?ref=' . $referenceId));
            }

            $paymentStatus = $this->verifyPhonePePayment($sale);

            if ($paymentStatus === 'SUCCESS') {
                $sale->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                Log::info('Payment successful via PhonePe webhook', [
                    'reference_id' => $referenceId
                ]);

                return redirect()->to(url('/payment-success?ref=' . $referenceId));
            } else {
                Log::warning('Payment not successful', [
                    'reference_id' => $referenceId,
                    'status' => $paymentStatus
                ]);

                return redirect()->to(url('/payment-failed?ref=' . $referenceId));
            }

        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->to(url('/payment-failed?ref=' . ($referenceId ?? 'unknown') . '&error=exception'));
        }
    }

    private function verifyPhonePePayment($sale)
    {
        try {
            $credentials = $this->getPaymentCredentials('phonepe', 'NATIONAL');

            if (!$credentials) {
                throw new \Exception('PhonePe credentials not configured');
            }

            $merchantId = $credentials['merchant_id'] ?? null;
            $environment = $credentials['environment'] ?? 'sandbox';

            if (!$merchantId || !$sale->payment_link_id) {
                throw new \Exception('Missing merchant ID or payment link ID');
            }

            $tokenService = app(PhonePeTokenService::class);
            $token = $tokenService->getAccessToken();

            if (!$token) {
                throw new \Exception('Failed to get PhonePe access token');
            }

            $apiUrl = ($environment === 'production')
                ? "https://api.phonepe.com/apis/pg/checkout/v2/status/{$merchantId}/{$sale->payment_link_id}"
                : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/status/{$merchantId}/{$sale->payment_link_id}";

            Log::info('Verifying PhonePe payment status', [
                'reference_id' => $sale->reference_id,
                'payment_link_id' => $sale->payment_link_id,
                'api_url' => $apiUrl
            ]);

            $response = \Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->get($apiUrl);

            $responseData = $response->json();

            Log::info('PhonePe Status Check Response', [
                'response' => $responseData
            ]);

            $status = $responseData['state'] ?? $responseData['code'] ?? 'UNKNOWN';

            return $status;

        } catch (\Exception $e) {
            Log::error('PhonePe Payment Verification Error', [
                'error' => $e->getMessage()
            ]);
            return 'FAILED';
        }
    }
}
