<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\PaymentMetadata;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhonePe\Env;
use PhonePe\payments\v2\models\request\builders\StandardCheckoutPayRequestBuilder;
use PhonePe\payments\v2\standardCheckout\StandardCheckoutClient;
use Illuminate\Support\Facades\Log;

class PhonePePaymentApiController extends ApiController
{
    protected $clientId, $clientSecret, $saltKey, $saltIndex, $merchantUserId, $callbackUrl, $phonePePaymentsClient;

    public function __construct()
    {
        $this->clientId = env('PHONEPE_CLIENT_ID');
        $this->clientSecret = env('PHONEPE_CLIENT_SECRET');
        $this->saltKey = env('PHONEPE_SALT_KEY');
        $this->saltIndex = (int) env('PHONEPE_SALT_INDEX', 1);
        $this->merchantUserId = env('PHONEPE_MERCHANT_USER_ID');
        $this->callbackUrl = env('PHONEPE_CALLBACK_URL');
        $env = strtolower(env('PHONEPE_ENV')) === 'prod' ? Env::PRODUCTION : Env::UAT;
        $this->phonePePaymentsClient = StandardCheckoutClient::getInstance(
            $this->clientId,
            1,
            $this->clientSecret,
            $env
        );
    }
    public function payment(Request $request): array|string
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone' => 'required|string',
            'email' => 'nullable|string'
        ]);

        $merchantTransactionID = StorageUtils::getNewName(10);
        $eventData = ['sample' => 'value'];

        $metaData = [
            'user_id' => $this->uid,
            'plan_id' => 'iwegi76',
            'amount' => $request->amount,
            'currency' => 'ind',
            'fromWallet' => 0,
            'coins' => 0,
            'from' => 'joy',
            'pay_mode' => 'upi',
            'code' => 'jdbited6hu',
            'eventData' => $eventData
        ];
        PaymentMetadata::create([
            'transaction_id' => $merchantTransactionID,
            'meta_data' => $metaData
        ]);

        $phonePeRequest = StandardCheckoutPayRequestBuilder::builder()
            ->merchantOrderId($merchantTransactionID)
            ->amount($request->amount * 100)
            ->redirectUrl('https://craftyartapp.com')
            ->message("Phone Pe Payment Integration")
            ->build();

        $response = $this->phonePePaymentsClient->pay($phonePeRequest);
        $response->transactionId = $merchantTransactionID;

//        $payPageUrl = $response->getInstrumentResponse()->getRedirectInfo()->getUrl();
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', ["data"=>$response]));
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            // Step 1: Get headers and body
            $headers = $request->headers->all();
            $body = $request->getContent();

            // Step 2: Your PhonePe credentials
            $username = env('PHONE_PE_WEBHOOK_USERNAME');
            $password = env('PHONE_PE_WEBHOOK_PASS');

            // Step 3: Verify callback authenticity
            $callbackResponse = $this->phonePePaymentsClient->verifyCallbackResponse(
                $headers,
                $body,
                $username,
                $password
            );

            // Step 4: Decode and handle response
            $data = json_decode($body, true);

            Log::info('PhonePe Webhook Payload:', $data);
            Log::info('PhonePe Webhook Payload: callbackResponse: ',$callbackResponse);
            $transactionId = $data['merchantTransactionId'] ?? null;
            if (!$transactionId) {
                return response()->json(['success' => false, 'message' => 'Missing transaction ID'], 400);
            }

            $paymentRecord = PaymentMetadata::where('transaction_id', $transactionId)->first();
            if (!$paymentRecord) {
                return response()->json(['success' => false, 'message' => 'Transaction not found'], 404);
            }

            $status = $data['code'] ?? 'UNKNOWN';
            $paymentRecord->update([
                'status' => $status,
                'meta_data' => array_merge(
                    (array) $paymentRecord->meta_data,
                    ['phonepe_callback' => $data]
                )
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('PhonePe Webhook Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

//    public function paymentWebhook(Request $request): array|string
//    {
//        $xVerify = $request->header('X-VERIFY');
//        $response = $request->response;
//        $decodedBase64 = base64_decode($response);
//        $decodedResponse = json_decode($decodedBase64, true);
//        $isValid = $this->phonePePaymentsClient->verifyCallback($response, $xVerify);
//        if (!$isValid) {
//            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Invalid Verification"));
//        }
//        $merchantTransactionID = $decodedResponse['data']['merchantTransactionId'] ?? null;
//        if (!$merchantTransactionID) {
//            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Merchant ID not found not found"));
//        }
//        $paymentRecord = PaymentMetadata::where('transaction_id', $merchantTransactionID)->first();
//        if (!$paymentRecord) {
//            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Transaction not found"));
//        }
//        $metaData = $paymentRecord->meta_data;
//        $currency_code = $metaData['currency'] ?? 'INR';
//        $user_id = $metaData['user_id'] ?? null;
//        $isManual = 0;
//        try {
//            $webhookResponse = Http::post('https://api.craftyartapp.com/api/payment/webhook', [
//                'method' => "phonepe",
//                'transaction_id' => $merchantTransactionID,
//                'currency_code' => $currency_code,
//                'user_id' => $user_id,
//                'isManual' => $isManual,
//                'showDecoded' => true
//            ]);
//
//            return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Success",['response'=>$webhookResponse->body()]));
//        } catch (\Exception $e) {
//        }
//        return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Something went wrong"));
//    }

    public function checkPaymentStatus(Request $request, $transactionId): array|string
    {
        try {
            $checkStatus = $this->phonePePaymentsClient->getOrderStatus($transactionId,true);
            $paymentRecord = PaymentMetadata::where('transaction_id', $transactionId)->first();
            if (!$paymentRecord) {
                return ResponseHandler::sendResponse($request, new ResponseInterface(404, false, 'Transaction not found'));
            }
            $metaData = $paymentRecord->meta_data;
            if (isset($metaData['eventData']) && is_string($metaData['eventData'])) {
                $decoded = json_decode($metaData['eventData'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $metaData['eventData'] = $decoded;
                }
            }
            return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Status Fetched', [
                'status' => $checkStatus,
                'metaData' => $metaData
            ]));
        } catch (\Exception $e) {
            return ResponseHandler::sendResponse($request, new ResponseInterface(500, false, 'Failed to fetch status', [
                'error' => $e->getMessage()
            ]));
        }
    }
}