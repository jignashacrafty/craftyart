# PhonePe QR Code - Database Error Fix

## ❌ Error

```
SQLSTATE[HY000]: General error: 1364 Field 'max_amount' doesn't have a default value
```

## 🔍 Root Cause

`phonepe_subscriptions` table માં insert કરતી વખતે required fields missing હતા:
- `max_amount`
- `currency`
- `frequency`
- `phonepe_subscription_id`
- `start_date`
- `next_billing_date`

## ✅ Solution

`PhonePeSubscription::create()` માં બધા required fields add કર્યા.

## 🔧 Fix Applied

### File: `app/Http/Controllers/Api/PhonePeAutoPayController.php`

**Before (Missing Fields):**
```php
PhonePeSubscription::create([
    'merchant_subscription_id' => $merchantSubscriptionId,
    'merchant_order_id' => $merchantOrderId,
    'phonepe_order_id' => $data['orderId'] ?? null,
    'user_id' => $request->user_id,
    'plan_id' => $request->plan_id,
    'amount' => $request->amount,
    'status' => 'PENDING',
    'subscription_status' => $data['state'] ?? 'PENDING',
    'metadata' => [...]
]);
```

**After (All Fields):**
```php
PhonePeSubscription::create([
    'merchant_subscription_id' => $merchantSubscriptionId,
    'merchant_order_id' => $merchantOrderId,
    'phonepe_order_id' => $data['orderId'] ?? null,
    'phonepe_subscription_id' => $data['orderId'] ?? null,  // ✅ Added
    'user_id' => $request->user_id,
    'plan_id' => $request->plan_id,
    'amount' => $request->amount,
    'currency' => 'INR',                                     // ✅ Added
    'frequency' => 'Monthly',                                // ✅ Added
    'max_amount' => $request->amount,                        // ✅ Added
    'start_date' => now()->toDateString(),                   // ✅ Added
    'next_billing_date' => now()->addMonth()->toDateString(), // ✅ Added
    'status' => 'PENDING',
    'subscription_status' => $data['state'] ?? 'PENDING',
    'metadata' => [...]
]);
```

## 📋 Added Fields

| Field | Value | Description |
|-------|-------|-------------|
| `phonepe_subscription_id` | `$data['orderId']` | PhonePe's order ID |
| `currency` | `'INR'` | Currency code |
| `frequency` | `'Monthly'` | Billing frequency |
| `max_amount` | `$request->amount` | Maximum amount for subscription |
| `start_date` | `now()->toDateString()` | Subscription start date |
| `next_billing_date` | `now()->addMonth()->toDateString()` | Next billing date |

## 🧪 Test Again

```bash
POST http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr

Body:
{
  "user_id": "test_user_123",
  "plan_id": "plan_monthly_99",
  "amount": 1,
  "upi": "vrajsurani606@okaxis",
  "target_app": "com.phonepe.app"
}
```

## ✅ Expected Result

હવે database error નહીં આવે અને QR code successfully generate થશે:

```json
{
  "statusCode": 200,
  "success": true,
  "msg": "QR Code generated successfully",
  "data": {
    "merchant_order_id": "MO_QR_...",
    "merchant_subscription_id": "MS_QR_...",
    "qr_code": {
      "base64": "data:image/png;base64,iVBORw0KGgoAAAANS...",
      "redirect_url": "https://mercury-t2.phonepe.com/...",
      "intent_url": "upi://pay?...",
      "decoded_params": {...}
    },
    "instructions": {...}
  }
}
```

## 📚 Database Schema

`phonepe_subscriptions` table માં આ fields required છે:

```sql
CREATE TABLE `phonepe_subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `merchant_subscription_id` varchar(255) NOT NULL,
  `merchant_order_id` varchar(255) NOT NULL,
  `phonepe_order_id` varchar(255) DEFAULT NULL,
  `phonepe_subscription_id` varchar(255) DEFAULT NULL,
  `user_id` varchar(255) NOT NULL,
  `plan_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) NOT NULL,
  `frequency` varchar(50) NOT NULL,
  `max_amount` decimal(10,2) NOT NULL,  -- ⚠️ Required field
  `start_date` date NOT NULL,
  `next_billing_date` date DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `subscription_status` varchar(50) DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
);
```

## ✅ Status

Database error fixed! હવે API કામ કરશે.
