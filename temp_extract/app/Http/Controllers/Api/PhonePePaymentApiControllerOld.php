<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\StorageUtils;
use App\Models\PaymentMetadata;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use PhonePe\Env;
use PhonePe\payments\v1\models\request\builders\InstrumentBuilder;
use PhonePe\payments\v1\models\request\builders\PgPayRequestBuilder;
use PhonePe\payments\v1\PhonePePaymentClient;

class PhonePePaymentApiControllerOld extends ApiController
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
        $this->phonePePaymentsClient = new PhonePePaymentClient(
            $this->clientId,
            $this->saltKey,
            $this->saltIndex,
            $env,
            true
        );
    }
    public function payment(Request $request): array|string
    {

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'phone' => 'required|string',
            'email' => 'nullable|string'
        ]);

        $merchantTransactionID = StorageUtils::getNewName(length: 10);
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
        $phonePerequest = PgPayRequestBuilder::builder()
                ->callbackUrl('https://thejahiratwala.in/insert.php')
            ->merchantId($this->clientId)
            ->merchantUserId($this->merchantUserId)
            ->amount($request->amount * 100)
            ->merchantTransactionId($merchantTransactionID)
            ->redirectUrl('https://craftyartapp.com')
            ->redirectMode("REDIRECT")
            ->paymentInstrument(InstrumentBuilder::buildPayPageInstrument())
            ->build();
        $response = $this->phonePePaymentsClient->pay($phonePerequest);
        $payPageUrl = $response->getInstrumentResponse()->getRedirectInfo()->getUrl();
        return ResponseHandler::sendResponse($request, new ResponseInterface(200, true, 'Loaded!', [
            "payPageUrl" => $payPageUrl,
            "transaction_id" => $merchantTransactionID,
        ]));
    }

    /*public function paymentWebhook(Request $request): JsonResponse
    {
        $xVerify = $request->header('X-VERIFY');
        $response = $request->response;
        $decodedBase64 = base64_decode($response);

        $decodedResponse = json_decode($decodedBase64, true);
        $isValid =  $this->phonePePaymentsClient->verifyCallback($response, $xVerify);
        PaymentMetadata::create([
            'transaction_id' => "efrgthyj",
            'meta_data' => json_encode($isValid),
        ]);
        return response()->json(['status' => $decodedResponse], 200);
    }*/

    public function paymentWebhook(Request $request): array|string
    {
        $xVerify = $request->header('X-VERIFY');
        $response = $request->response;
        $decodedBase64 = base64_decode($response);
        $decodedResponse = json_decode($decodedBase64, true);
        $isValid = $this->phonePePaymentsClient->verifyCallback($response, $xVerify);
        if (!$isValid) {
            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Invalid Verification"));
        }
        $merchantTransactionID = $decodedResponse['data']['merchantTransactionId'] ?? null;
        if (!$merchantTransactionID) {
            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Merchant ID not found not found"));
        }
        $paymentRecord = PaymentMetadata::where('transaction_id', $merchantTransactionID)->first();
        if (!$paymentRecord) {
            return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Transaction not found"));
        }
        $metaData = $paymentRecord->meta_data;
        $currency_code = $metaData['currency'] ?? 'INR';
        $user_id = $metaData['user_id'] ?? null;
        $isManual = 0;
        try {
            $webhookResponse = Http::post('https://api.craftyartapp.com/api/payment/webhook', [
                'method' => "phonepe",
                'transaction_id' => $merchantTransactionID,
                'currency_code' => $currency_code,
                'user_id' => $user_id,
                'isManual' => $isManual,
                'showDecoded' => true
            ]);

            return ResponseHandler::sendResponse($request,new ResponseInterface(200,true,"Success",['response'=>$webhookResponse->body()]));
        } catch (\Exception $e) {
        }
        return ResponseHandler::sendResponse($request,new ResponseInterface(403,false,"Something went wrong"));
    }

    public function checkPaymentStatus(Request $request, $transactionId): array|string
    {
        try {
            $checkStatus = $this->phonePePaymentsClient->statusCheck($transactionId);
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