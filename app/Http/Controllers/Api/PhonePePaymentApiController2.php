<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentGatewayEnum;
use App\Http\Controllers\AiCreditController;
use App\Models\Attire;
use App\Models\Design;
use App\Models\PaymentConfiguration;
use App\Models\PhonepeWebhook;
use App\Models\Pricing\SubPlan;
use App\Models\PurchaseHistory;
use App\Models\Subscription;
use App\Models\UserData;
use App\Models\Video\VideoTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PhonePe\Env;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Illuminate\Support\Facades\Log;

class PhonePePaymentApiController2 extends ApiController
{
    private static $FREE_TEMPLATE_DISCOUNT = "50";
    protected $clientId, $clientSecret, $clientVersion, $merchantUserId, $callbackUrl, $phonePePaymentsClient, $webhookUsername, $webhookPassword, $phonePeEnable;

    private $production = true;

    public function __construct()
    {
        $nationalPaymentGateway = PaymentConfiguration::whereGateway(PaymentGatewayEnum::PHONEPE->value)
            ->where('is_active', 1)
            ->first();
        if ($nationalPaymentGateway) {
            $this->phonePeEnable = true;
            // Get credentials from the PaymentConfiguration
            $credentials = $nationalPaymentGateway->credentials;
            // Assign credentials from database
            $this->clientId = $credentials['client_id'] ?? '';
            $this->clientSecret = $credentials['client_secret'] ?? '';
            $this->merchantUserId = $credentials['merchant_id'] ?? '';
            $this->clientVersion = $credentials['client_version'] ?? '';
            $this->webhookUsername = $credentials['webhook_username'] ?? '';
            $this->webhookPassword = $credentials['webhook_password'] ?? '';


            $this->callbackUrl = "https://www.craftyartapp.com/";


            $env = $this->production ? Env::PRODUCTION : Env::UAT;


            // Initialize PhonePe client
            try {
                $this->phonePePaymentsClient = StandardCheckoutClient::getInstance(
                    $this->clientId,
                    $this->clientVersion,
                    $this->clientSecret,
                    Env::PRODUCTION
                );
            } catch (\Exception $e) {
                Log::error('Failed to initialize PhonePe client: ' . $e->getMessage());
                $this->phonePeEnable = false;
            }
        } else {
            $this->phonePeEnable = false;
        }
    }



    public function payment(Request $request): array|string
    {
        if (!$this->phonePeEnable) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, 'Payment Is Not Enable',));
        }

//        $request->validate([
//            'amount' => 'required',
//            'phone' => 'required|string',
//            'email' => 'nullable|string'
//        ]);

        $p = $request->get("p");
        $code = $request->get("code") ?? "";
        $name = $request->get("name");
        $number = $request->get("number");
        $email = $request->get("email");

        $userData = UserData::whereEmail($email)->first();
        $currency = "INR";

        $paymentDetails = self::getPaymentDetails($request,$userData,$currency,$p,$code,false);

        $amount = $paymentDetails['amount'];
        $merchantOrderId = "PHONEPE_".Carbon::now()->timestamp;

        if($paymentDetails['payMode'] == "old_sub") {
            $phonePeRequest = StandardCheckoutPayRequestBuilder::builder()
                ->merchantOrderId($merchantOrderId)
                ->amount($amount * 100)
                ->redirectUrl('https://craftyartapp.com')
                ->message("Phone Pe Payment Integration")
                ->build();
            $response = $this->phonePePaymentsClient->pay($phonePeRequest);
            $response->merchantOrderID = $merchantOrderId;
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', ["data" => $response]));
        } else {
            $token = $this->getAccessToken();

            $merchantOrderId = 'ORDER_' . Str::uuid();
            $merchantSubscriptionId = 'SUB_' . Str::uuid();

            $payload = [
                "merchantId" => $this->merchantUserId,
                "merchantOrderId" => $merchantOrderId,
                "merchantUserId" => $this->merchantUserId,
                "amount" => $amount * 100,

                "paymentFlow" => [
                    "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
                    "message" => "Monthly Subscription",
                    "merchantUrls" => [
                        "redirectUrl" => $this->callbackUrl,
                        "cancelRedirectUrl" => $this->callbackUrl,
                    ],
                    "subscriptionDetails" => [
                        "subscriptionType" => "RECURRING",
                        "merchantSubscriptionId" => $merchantSubscriptionId,
                        "authWorkflowType" => "TRANSACTION",
                        "amountType" => "FIXED",
                        "maxAmount" => "100" * 100,
                        "frequency" => "Monthly",
                        "productType" => "UPI_MANDATE",
                        "expireAt" => now()->addMonths(12)->timestamp * 1000,
                    ],
                ],
                "expireAfter" => 3000,
            ];

            $phonePeUrl = $this->production ? "https://api.phonepe.com/apis/pg/checkout/v2/pay" : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay";

            $response = Http::withHeaders([
                'Authorization' => 'O-Bearer ' . $token,
                'Content-Type' => 'application/json',
            ])->post($phonePeUrl,
                $payload
            );

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Success',[
                'payload' => $payload,
                'response' => $response->json(),
            ]));
        }
    }



//    public function refundPhonePePayment(Request $request): mixed
//    {
//        $merchantRefundId = "REFUND_" . StorageUtils::getNewName(7);
//        $merchantOrderId = $request->get("merchant_order_id");
//        $amount = $request->amount * 100;
//        $refundRequest = StandardCheckoutRefundRequestBuilder::builder()
//            ->merchantRefundId($merchantRefundId)
//            ->originalMerchantOrderId($merchantOrderId)
//            ->amount($amount)
//            ->build();
//
//        try {
//            $response =  $this->phonePePaymentsClient->refund($refundRequest);
//            return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Success",['data' => $response]));
//        } catch (\Exception $e) {
//            return ResponseHandler::sendResponse($request,new ResponseInterface(500,false,$e->getMessage()));
//        }
//    }

    private function getAccessToken() {
//        $accessToken = $this->production ? 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token' : "https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token";
        $accessTokenUrl = 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token';
        try {
//            $accessToken = "https://api-preprod.phonepe.com/apis/pg-sandbox/v1/oauth/token'";

            Log::info("Client Id -> " . $this->clientId . " Client Secret -> " . $this->clientSecret . " Client Version -> " . $this->clientVersion);

            $response = Http::asForm()->post(
                $accessTokenUrl,
                [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'client_version' => $this->clientVersion,
                    'grant_type' => 'client_credentials',
                ]
            );

            $data = $response->json();

            if (!isset($data['access_token'])) {
                Log::error('PhonePe OAuth Error', $data);

                abort(500, 'PhonePe OAuth failed: ' . json_encode($data));
            }
            Log::info("Token Response ".json_encode($data));
            return $data['access_token'];
        } catch (\Exception $e){
            Log::info("PhonePe Token Error ". $e->getMessage());
            return $e->getMessage();
        }
    }

    public function refundPhonePePayment(Request $request)
    {
        $token = $this->getAccessToken();
//        try {
//            $token = $this->phonePePaymentsClient->getAuthHeadersToken();
//        } catch (PhonePeException $e) {
//            return ResponseHandler::sendResponse($request,new ResponseInterface(200,false,$e->getMessage()));
//        }

        $payload = [
//            "merchantId" => $this->merchantUserId,
            "merchantRefundId" => "REFUND_" . uniqid(),
            "originalMerchantOrderId" => $request->merchant_order_id,
            "amount" => (int) ($request->amount * 100),
        ];

        Log::info("Payload Request ".json_encode($payload));

        $refundUrl = "https://api.phonepe.com/apis/pg/payments/v2/refund";
//        $refundUrl = $this->production
//            ? "https://api.phonepe.com/apis/pg/payments/v2/refund"
//            : "https://api-preprod.phonepe.com/apis/pg-sandbox/payments/v2/refund";

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ])->post($refundUrl, $payload);

        Log::info("PhonePe Api Response ".json_encode($response->json()));

        return ResponseHandler::sendResponse(
            $request,
            new ResponseInterface(
                200,
                $response->successful(),
                $response->successful() ? 'Refund initiated' : 'Refund failed',
                $response->json()
            )
        );
    }

    public function webhook(Request $request)
    {
        try {
            $headers = $request->headers->all();
            $body = $request->getContent();

            $callbackResponse = $this->phonePePaymentsClient->verifyCallbackResponse(
                $headers,
                $body,
                $this->webhookUsername,
                $this->webhookPassword
            );

            $data = json_decode($body, true);

            Log::info('PhonePe Webhook Payload:', $data);
            Log::info('PhonePe Webhook Payload: callbackResponse: ', $callbackResponse);

            $transactionId = $data['merchantTransactionId'] ?? null;
            $status = $data['code'] ?? 'UNKNOWN';

            $phonePeWebhook = new PhonepeWebhook();
            $phonePeWebhook->transaction_id = $transactionId;
            $phonePeWebhook->json = $body;
            $phonePeWebhook->code = $status;
            $phonePeWebhook->save();

//            return response()->json(['success' => true]);
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, "Successfully Called"));
        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Error: ' . $e->getMessage());
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, $e->getMessage(),));
        }
    }



    public function createAutopayMandate(Request $request)
    {
        if (!$this->phonePeEnable) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, 'Payment Is Not Enable',));
        }
        $token = $this->getAccessToken();

        $merchantOrderId = 'ORDER_' . Str::uuid();
        $merchantSubscriptionId = 'SUB_' . Str::uuid();

        $payload = [
            "merchantId" => $this->merchantUserId,
            "merchantOrderId" => $merchantOrderId,
            "merchantUserId" => $this->merchantUserId,

            // ✅ REQUIRED
            "amount" => $request->amount * 100,

            "paymentFlow" => [
                "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
                "message" => "Monthly Subscription",
                "merchantUrls" => [
                    "redirectUrl" => $this->callbackUrl,
                    "cancelRedirectUrl" => $this->callbackUrl,
                ],
                "subscriptionDetails" => [
                    "subscriptionType" => "RECURRING",
                    "merchantSubscriptionId" => $merchantSubscriptionId,
                    "authWorkflowType" => "TRANSACTION",
                    "amountType" => "FIXED",
                    "maxAmount" => "100" * 100,
                    "frequency" => "Monthly",
                    "productType" => "UPI_MANDATE",
                    "expireAt" => now()->addMonths(12)->timestamp * 1000,
                ],
            ],
            "expireAfter" => 3000,
        ];

        $phonePeUrl = $this->production ? "https://api.phonepe.com/apis/pg/checkout/v2/pay" : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay";

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->post($phonePeUrl,
            $payload
        );

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Success',[
            'payload' => $payload,
            'response' => $response->json(),
        ]));
    }

    public function checkOrderStatus(Request $request,string $merchantOrderId): array|string
    {
        if (!$this->phonePeEnable) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, 'Payment Is Not Enable',));
        }
        $token = $this->getAccessToken(); // your existing working method

        $url = $this->production
            ? "https://api.phonepe.com/apis/pg/checkout/v2/order/{$merchantOrderId}/status"
            : "https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/order/{$merchantOrderId}/status";

        $response = Http::withHeaders([
            'Authorization' => 'O-Bearer ' . $token,
            'Content-Type' => 'application/json',
        ])->get($url);

        if (!$response->successful()) {
            Log::error('PhonePe Order Status Failed', [
                'order_id' => $merchantOrderId,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, 'Unable to fetch order status',));
        }

        return ResponseHandler::sendResponse($request, new ResponseInterface(200, false, 'Status Fetch',["data"=>$response->json()]));
    }

    public function checkPaymentStatus(Request $request, $transactionId): array|string
    {
        try {
            $checkStatus = $this->phonePePaymentsClient->getOrderStatus($transactionId, true);

            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Status Fetched', [
                'status' => $checkStatus,
            ]));
        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, 'Failed to fetch status', [
                'error' => $e->getMessage()
            ]));
        }
    }

    private function getPaymentDetails(Request $request, UserData $user_data, $currency, $assetDetails, $code, $offerApplied): array
    {
        $validity = 0;
        $tempAssetData = json_decode($assetDetails);

        if ($tempAssetData && !is_numeric($tempAssetData)) {
            $assetDatas = $this->getAssetData(RateController::getRates(true), $tempAssetData, $currency, $this->uid, $offerApplied);
            if (!$assetDatas['success']) return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, $assetDatas['message']));

            $payMode = $assetDatas['payMode'];
            if ($payMode === 'ai_credit') $description = 'AI Credits';
            else $description = 'Premium Assets';
            $amount = $assetDatas['message'];
            $assetDetails = json_encode($assetDatas['datas']);
        } else {
            $newPlan = SubPlan::with(['plan', 'duration'])->where('string_id', $assetDetails)->first();
            if ($this->isTester() && $newPlan && $newPlan->plan && $newPlan->duration && !array_diff(SubPlan::$PLAN_KEYS, array_keys($newPlan->plan_details))) {
                $description = $newPlan->plan->name;
                if (strtoupper($currency) == "INR") $amount = $newPlan->plan_details["inr_offer_price"];
                else $amount = $newPlan->plan_details["usd_offer_price"];
                $payMode = 'new_sub';
                $validity = $newPlan->duration->duration;
            } else {
                if (in_array($assetDetails, PaymentController::$OFFER_IDS)) $res = Subscription::find($assetDetails);
                else $res = Subscription::whereId($assetDetails)->whereStatus(1)->first();
                if (!$res) return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, 'Sub Error'));
                $description = $res->package_name;
                if (strtoupper($currency) == "INR") $amount = $res->price;
                else $amount = $res->price_dollar;
                $payMode = 'old_sub';
                $validity = $res->validity;
            }
        }

        if ($amount <= 0) return ResponseHandler::sendRealResponse(new ResponseInterface(401, false, 'Amount error'));

        if ($user_data->cheap_rate == 1 || $user_data->cheap_rate == "1") $amount = 1;

        $promoCodeId = 0;
//        $promoCode = $this->cpm($amount, $code, $currency, true);
//        if ($promoCode['success']) {
//            $amount = $promoCode['amount'];
//            $promoCodeId = $promoCode['id'];
//        }

        $eventData = [
            '_fbclid' => $request->cookie('_fbclid'),
            '_caid' => $request->cookie('_caid'),
            '_gclid' => $request->cookie('_gclid'),
            '_ga' => $request->cookie('_ga'),
            '_gcl_au' => $request->cookie('_gcl_au'),
            'userAgent' => $request->header('User-Agent', 'Unknown'),
            'clientIp' => ApiController::findIp($request) ?? '0.0.0.0',
        ];

        return ResponseHandler::sendRealResponse(new ResponseInterface(200, true, 'success', [
            'amount' => $amount,
            'payMode' => $payMode,
            'promoCodeId' => $promoCodeId,
            'description' => $description,
            'eventData' => $eventData,
            'assetDetails' => $assetDetails,
            'validity' => $validity
        ]));
    }

    private function getAssetData($rates, $assetDetails, $currency, $uid, $offerApplied): array
    {
        $payMode = null;
        $amount = 0;
        $paymentDatas = [];
        foreach ($assetDetails as $assetDetail) {
            if ($assetDetail->type == 0 || $assetDetail->type == '0') {
                if ($assetDetail->id != 'draft') {
                    if (!PurchaseHistory::where('user_id', $uid)->where('product_id', $assetDetail->id)->exists()) {
                        $desData = Design::where('string_id', $assetDetail->id)->where('status', 1)->first();
                        if ($desData) {

                            $containPremium = $desData->is_premium == 1 || $desData->is_freemium == 1;

                            $thumbArray = json_decode($desData->thumb_array);
                            $size = sizeof($thumbArray);

                            $pyt = RateController::getTemplateRates($rates, $size, $desData, !$containPremium && $offerApplied ? self::$FREE_TEMPLATE_DISCOUNT : 0);
                            $pyt['id'] = $desData->string_id;
                            $pyt['type'] = 0;
                            $paymentDatas[] = $pyt;
                            $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                            if (is_null($payMode)) $payMode = "template";
                        } else {
                            $response['success'] = false;
                            $response['message'] = 'Data Error';
                            return $response;
                        }
                    }
                }
            } else if ($assetDetail->type == 4 || $assetDetail->type == '4') {
                $desData = VideoTemplate::where('string_id', $assetDetail->id)->first();
                if ($desData) {
                    $size = $desData->pages;
                    $pyt = RateController::getVideoRates($rates, $size);
                    $pyt['id'] = $desData->string_id;
                    $pyt['type'] = 4;
                    $paymentDatas[] = $pyt;
                    $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                    if (is_null($payMode)) $payMode = "video";
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Data Error';
                    return $response;
                }
            } else if ($assetDetail->type == 5 || $assetDetail->type == '5') {
                $desData = Attire::where('string_id', $assetDetail->id)->first();
                if ($desData) {
                    $size = $desData->head_count;
                    $pyt = RateController::getCaricatureRates($rates, $size, false, $desData->editor_choice == 1);
                    $pyt['id'] = $desData->string_id;
                    $pyt['type'] = 5;
                    $paymentDatas[] = $pyt;
                    $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                    if (is_null($payMode)) $payMode = "caricature";
                } else {
                    $response['success'] = false;
                    $response['message'] = 'Data Error';
                    return $response;
                }
            } else if ($assetDetail->type == 6 || $assetDetail->type == '6') {
                $pyt = AiCreditController::getData($assetDetail->id);
                if (!$pyt) {
                    $response['success'] = false;
                    $response['message'] = 'Invalid type';
                    return $response;
                }
                $pyt['id'] = $assetDetail->id;
                $pyt['type'] = 6;
                $paymentDatas[] = $pyt;
                $amount += $currency == 'INR' ? $pyt['inrVal'] : $pyt['usdVal'];
                if (is_null($payMode)) $payMode = "ai_credit";
            } else {
                $response['success'] = false;
                $response['message'] = 'Invalid type';
                return $response;
            }
        }

        if ($amount <= 0 || is_null($payMode)) {
            $response['success'] = false;
            $response['message'] = 'Invalid amount';
        } else {
            $response['success'] = true;
            $response['message'] = $amount;
            $response['datas'] = $paymentDatas;
            $response['payMode'] = $payMode;
        }
        return $response;
    }

}