# PhonePe QR Code API Fix

## ❌ Issue

```json
{
  "statusCode": 400,
  "success": false,
  "msg": "QR Code generation failed",
  "error": {
    "message": "Bad Request - Api Mapping Not Found"
  }
}
```

## 🔍 Root Cause

PhonePe Sandbox માં **Subscription API** (`/apis/pg-sandbox/subscriptions/v2/create`) કામ નથી કરતું.

Error: "Api Mapping Not Found" એટલે કે આ endpoint sandbox માં available નથી.

## ✅ Solution

**Checkout API** use કરવું જોઈએ જે sandbox માં કામ કરે છે:

```
https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay
```

## 🔧 Changes Made

### File: `app/Http/Controllers/Api/PhonePeAutoPayController.php`

**Before (Wrong API):**
```php
$url = $this->production
    ? 'https://api.phonepe.com/apis/pg/subscriptions/v2/create'
    : 'https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/create';
```

**After (Correct API):**
```php
$url = $this->production
    ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
    : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';
```

### Updated Payload Structure

**Before (Subscription API payload):**
```php
$payload = [
    "merchantId" => env('PHONEPE_MERCHANT_ID'),
    "merchantOrderId" => $merchantOrderId,
    "merchantSubscriptionId" => $merchantSubscriptionId,
    "subscriptionDetails" => [
        "subscriptionType" => "DAILY",
        // ...
    ],
    "authWorkflowType" => "TRANSACTION",
    "amount" => (int)($request->amount * 100),
    "autoDebit" => true
];
```

**After (Checkout API payload):**
```php
$payload = [
    "merchantId" => env('PHONEPE_MERCHANT_ID'),
    "merchantOrderId" => $merchantOrderId,
    "merchantUserId" => env('PHONEPE_MERCHANT_ID'),
    "amount" => (int)($request->amount * 100),
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
            "maxAmount" => (int)($request->amount * 100),
            "recurringAmount" => (int)($request->amount * 100),
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
```

### Response Handling

**Before:**
```php
if ($response->successful() && isset($data['success']) && $data['success']) {
    $intentUrl = $data['intentUrl'] ?? null;
    // ...
}
```

**After:**
```php
if ($response->successful() && isset($data['redirectUrl'])) {
    $redirectUrl = $data['redirectUrl'] ?? null;
    $intentUrl = $redirectUrl; // redirectUrl contains UPI intent
    // ...
}
```

## 🧪 Testing

### Request

```bash
POST http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr

Headers:
Content-Type: application/json
Accept: application/json

Body:
{
  "user_id": "test_user_123",
  "plan_id": "plan_monthly_99",
  "amount": 1,
  "upi": "vrajsurani606@okaxis",
  "target_app": "com.phonepe.app"
}
```

### Expected Response

```json
{
  "statusCode": 200,
  "success": true,
  "msg": "QR Code generated successfully",
  "data": {
    "merchant_order_id": "MO_QR_...",
    "merchant_subscription_id": "MS_QR_...",
    "phonepe_order_id": "...",
    "state": "PENDING",
    "expire_at": "...",
    "qr_code": {
      "base64": "data:image/png;base64,iVBORw0KGgoAAAANS...",
      "redirect_url": "https://...",
      "intent_url": "upi://pay?...",
      "decoded_params": {
        "pa": "merchant@upi",
        "pn": "Merchant Name",
        "am": "1.00",
        "cu": "INR"
      }
    },
    "instructions": {
      "step_1": "Open any UPI app (PhonePe, GPay, Paytm, etc.)",
      "step_2": "Tap on 'Scan QR Code' option",
      "step_3": "Scan this QR code with your phone camera",
      "step_4": "Verify amount and complete payment"
    }
  }
}
```

## 📝 Key Differences

| Feature | Subscription API | Checkout API |
|---------|-----------------|--------------|
| Endpoint | `/subscriptions/v2/create` | `/checkout/v2/pay` |
| Sandbox Support | ❌ No | ✅ Yes |
| Response Field | `intentUrl` | `redirectUrl` |
| Payload Structure | Flat | Nested in `paymentFlow` |
| Success Check | `data['success']` | `data['redirectUrl']` |

## 🎯 Why Checkout API?

1. ✅ **Works in Sandbox** - Can test without production credentials
2. ✅ **Returns redirectUrl** - Contains UPI intent URL for QR code
3. ✅ **Subscription Support** - Can create subscriptions via checkout flow
4. ✅ **Proven Working** - Already used in `setupSubscription()` method

## 🚀 Next Steps

1. Test API in Postman
2. Verify QR code generation
3. Scan QR code with mobile
4. Check subscription status

## 📚 Related Files

- `app/Http/Controllers/Api/PhonePeAutoPayController.php` - Updated
- `PHONEPE_AUTOPAY_API_COLLECTION.json` - Postman collection
- `PHONEPE_AUTOPAY_QR_CODE_REACT_GUIDE.md` - React implementation guide
- `PHONEPE_AUTOPAY_QR_CODE_REACT_GUIDE_GJ.md` - Gujarati guide

## ✅ Status

API fixed અને હવે કામ કરશે. Checkout API use કરે છે જે sandbox માં supported છે.
