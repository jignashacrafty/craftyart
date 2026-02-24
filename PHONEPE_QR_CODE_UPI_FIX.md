# PhonePe QR Code UPI URL Fix

## Problem
The QR code generation API was using the CHECKOUT API which returns HTTP/HTTPS URLs instead of UPI intent URLs (`upi://mandate?...`). This prevented proper QR code scanning with UPI apps for mandate setup.

## Root Cause
- Using `CHECKOUT API` with `UPI_INTENT` payment mode returns HTTPS redirect URLs
- These HTTPS URLs are meant for web redirects, not QR code scanning
- UPI apps need the `upi://mandate?...` format to recognize and process mandate setup

## Solution
Changed the `generateQRCode` method to use the Subscription API v2 with `UPI_QR` payment mode instead of the CHECKOUT API.

## Changes Made

### 1. `generateQRCode` Method - Complete Rewrite
**Before:**
- Used CHECKOUT API (`/apis/pg/checkout/v2/pay`)
- Payment mode: `UPI_INTENT` with `targetApp`
- Returned HTTPS URLs like `https://mercury-t2.phonepe.com/transact/pgv3?token=...`

**After:**
- Uses Subscription API v2 (`/apis/pg/subscriptions/v2/setup`)
- Payment mode: `UPI_QR` (specifically for QR code generation)
- Returns UPI URLs like `upi://mandate?mn=Autopay&ver=01&...`

### 2. API Endpoint Changes
```php
// OLD - CHECKOUT API
$url = $this->production
    ? 'https://api.phonepe.com/apis/pg/checkout/v2/pay'
    : 'https://api-preprod.phonepe.com/apis/pg-sandbox/checkout/v2/pay';

// NEW - Subscription API v2
$url = $this->production
    ? "https://api.phonepe.com/apis/pg/subscriptions/v2/setup"
    : "https://api-preprod.phonepe.com/apis/pg-sandbox/subscriptions/v2/setup";
```

### 3. Payload Structure Changes
```php
// OLD - CHECKOUT API payload
$payload = [
    "merchantId" => env('PHONEPE_MERCHANT_ID'),
    "merchantOrderId" => $merchantOrderId,
    "merchantUserId" => env('PHONEPE_MERCHANT_ID'),
    "amount" => (int) ($request->amount * 100),
    "paymentFlow" => [
        "type" => "SUBSCRIPTION_CHECKOUT_SETUP",
        "paymentMode" => [
            "type" => "UPI_INTENT",
            "targetApp" => "com.phonepe.app",
        ],
        // ... more fields
    ]
];

// NEW - Subscription API v2 payload
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
            "type" => "UPI_QR", // Key change - returns UPI URL
        ],
    ],
    "deviceContext" => [
        "deviceOS" => "ANDROID"
    ],
];
```

### 4. Response Structure
The response now properly contains UPI URLs:

```json
{
  "statusCode": 200,
  "success": true,
  "msg": "QR Code generated successfully",
  "merchant_order_id": "MO_QR_xxx",
  "merchant_subscription_id": "MS_QR_xxx",
  "phonepe_order_id": "OMOxxx",
  "state": "PENDING",
  "expire_at": 1771848332788,
  "qr_code": {
    "base64": {
      "url_for_qr": "upi://mandate?mn=Autopay&ver=01&...",
      "raw_upi_string": "upi://mandate?mn=Autopay&ver=01&...",
      "instructions": {
        "en": "Scan this QR code with any UPI app to set up AutoPay mandate",
        "hi": "AutoPay mandate सेट करने के लिए किसी भी UPI ऐप से इस QR कोड को स्कैन करें",
        "gu": "AutoPay mandate સેટ કરવા માટે કોઈપણ UPI એપ્લિકેશન વડે આ QR કોડ સ્કેન કરો"
      },
      "note": "Use this UPI URL (upi://mandate?...) in React QR code library"
    },
    "intent_url": "upi://mandate?mn=Autopay&ver=01&...",
    "decoded_params": { ... }
  }
}
```

## API Endpoints

### Generate QR Code
```
POST /api/phonepe/autopay/generate-qr
```

**Request:**
```json
{
  "user_id": "user123",
  "plan_id": "plan456",
  "amount": 99
}
```

**Response:**
```json
{
  "statusCode": 200,
  "success": true,
  "msg": "QR Code generated successfully",
  "merchant_order_id": "MO_QR_xxx",
  "merchant_subscription_id": "MS_QR_xxx",
  "phonepe_order_id": "OMOxxx",
  "state": "PENDING",
  "expire_at": 1771848332788,
  "qr_code": {
    "base64": {
      "url_for_qr": "upi://mandate?...",
      "raw_upi_string": "upi://mandate?...",
      "instructions": { ... },
      "note": "Use this UPI URL in React QR code library"
    },
    "intent_url": "upi://mandate?...",
    "decoded_params": { ... }
  },
  "instructions": {
    "step_1": "Open any UPI app (PhonePe, GPay, Paytm, etc.)",
    "step_2": "Tap on \"Scan QR Code\" option",
    "step_3": "Scan this QR code with your phone camera",
    "step_4": "Verify amount and complete mandate setup"
  }
}
```

## Frontend Implementation

Use the `qr_code.base64.url_for_qr` field to generate QR codes:

```javascript
import QRCode from 'qrcode.react';

// From API response
const { qr_code } = response;

// Generate QR code with UPI URL
<QRCode 
  value={qr_code.base64.url_for_qr} 
  size={256}
  level="H"
/>
```

Or with `react-qr-code`:

```javascript
import { QRCodeSVG } from 'react-qr-code';

<QRCodeSVG 
  value={qr_code.base64.url_for_qr} 
  size={256}
  level="H"
/>
```

## Testing Steps

1. Call the API:
   ```bash
   POST /api/phonepe/autopay/generate-qr
   {
     "user_id": "test123",
     "plan_id": "plan456",
     "amount": 1
   }
   ```

2. Verify response contains:
   - `qr_code.base64.url_for_qr` starts with `upi://mandate?`
   - `qr_code.intent_url` starts with `upi://mandate?`
   - NOT HTTPS URLs

3. Generate QR code using the UPI URL

4. Scan with any UPI app (PhonePe, GPay, Paytm, etc.)

5. Verify the mandate setup screen appears with:
   - Merchant name
   - Amount
   - Frequency (Monthly)
   - Validity period

## Important Notes

- **UPI_QR vs UPI_INTENT**: 
  - `UPI_QR` returns `upi://mandate?...` format for QR codes
  - `UPI_INTENT` returns HTTPS URLs for app-to-app redirects
  
- **API Compatibility**:
  - Subscription API v2 is the correct API for mandate setup
  - CHECKOUT API is for one-time payments, not recurring mandates

- **QR Code Format**:
  - Must use `upi://mandate?...` format
  - HTTPS URLs will not work with UPI app QR scanners
  - The UPI URL contains all mandate parameters encoded

- **Universal Compatibility**:
  - QR codes work with ANY UPI app (PhonePe, GPay, Paytm, BHIM, etc.)
  - No need to specify target app for QR codes
  - Users can choose their preferred UPI app to scan
