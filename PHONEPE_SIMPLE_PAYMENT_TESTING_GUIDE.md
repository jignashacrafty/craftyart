# PhonePe Simple Payment Testing Guide

## 📋 Overview

This guide explains how to test the PhonePe AutoPay subscription payment system using both the web interface and Postman API collection.

## 🌐 Web Interface Testing

### Access the Test Page

**URL:** `http://localhost/git_jignasha/craftyart/public/phonepe/simple-payment-test`

**Requirements:**
- Must be logged in as Admin
- Browser with JavaScript enabled

### Features Available

1. **Payment Request Form**
   - Enter your UPI ID (e.g., `vrajsurani606@okaxis`)
   - Enter amount (minimum ₹1)
   - Enter mobile number
   - Click "📲 Send AutoPay Request to My UPI"

2. **Transaction History Table**
   - View all past transactions
   - See real-time status updates
   - Copy IDs with one click
   - Action buttons for each transaction

3. **Action Buttons**
   - **🔍 Status** - Check current subscription status
   - **📧 Pre-Debit** - Check if subscription is ready for debit
   - **💳 Debit** - Trigger real payment (⚠️ charges money!)
   - **🧪 Simulate** - Test flow without real payment

---

## 🔧 API Testing with Postman

### Step 1: Import Collection

1. Open Postman
2. Click "Import" button
3. Select file: `PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json`
4. Collection will be imported with all 7 endpoints

### Step 2: Configure Environment Variables

Set these variables in Postman:

| Variable | Value | Description |
|----------|-------|-------------|
| `base_url` | `http://localhost/git_jignasha/craftyart/public` | Your application base URL |
| `csrf_token` | Get from browser | Required for POST requests |

#### How to Get CSRF Token:

**Method 1: From Browser Console**
```javascript
// Open browser console (F12) on any logged-in page
document.querySelector('meta[name="csrf-token"]').content
```

**Method 2: From Cookies**
- Open DevTools → Application → Cookies
- Find `XSRF-TOKEN` cookie value
- Decode it (it's URL encoded)

**Method 3: From Page Source**
```html
<!-- Look for this in page source -->
<meta name="csrf-token" content="YOUR_TOKEN_HERE">
```

### Step 3: Test Flow

#### 🔹 Test 1: Create Subscription

**Endpoint:** `1. Send Payment Request (Create Subscription)`

**Request:**
```
POST /phonepe/send-payment-request
Content-Type: application/x-www-form-urlencoded

upi_id=vrajsurani606@okaxis
amount=1
mobile=9724085965
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Payment request sent successfully!",
  "data": {
    "merchant_order_id": "MO1234567890",
    "merchant_subscription_id": "MS1234567890",
    "order_id": "OMO1234567890",
    "state": "PENDING",
    "upi_id": "vrajsurani606@okaxis",
    "amount": 1
  }
}
```

**📝 Important:** Save the `merchant_subscription_id` from response!

**What Happens:**
1. OAuth token is generated/retrieved from cache
2. Subscription setup request sent to PhonePe
3. User receives UPI notification on phone
4. Records created in database tables:
   - `phonepe_autopay_test_history`
   - `phonepe_transactions`
   - `phonepe_notifications`

---

#### 🔹 Test 2: Check Status

**Endpoint:** `2. Check Subscription Status`

**Request:**
```
POST /phonepe/check-subscription-status
Content-Type: application/x-www-form-urlencoded

merchantSubscriptionId=MS1234567890
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "state": "ACTIVE",
    "subscriptionId": "SUB_PHONEPE_ID",
    "merchantSubscriptionId": "MS1234567890",
    "amount": 100,
    "frequency": "Monthly"
  }
}
```

**Possible States:**
- `PENDING` - User hasn't approved yet
- `ACTIVE` - ✅ Ready for auto-debit
- `COMPLETED` - Subscription completed
- `FAILED` - Setup failed
- `CANCELLED` - User cancelled

**When to Use:**
- After sending payment request
- Before triggering auto-debit
- To verify user approval

---

#### 🔹 Test 3: Pre-Debit Check

**Endpoint:** `3. Send Pre-Debit Notification`

**Request:**
```
POST /phonepe/send-predebit
Content-Type: application/x-www-form-urlencoded

merchantSubscriptionId=MS1234567890
amount=1
```

**Expected Response:**
```json
{
  "success": true,
  "message": "✅ Subscription is ACTIVE and ready!...",
  "phonepe_subscription_id": "SUB_PHONEPE_ID",
  "merchant_subscription_id": "MS1234567890",
  "subscription_state": "ACTIVE",
  "note": "PhonePe OAuth API: Pre-debit notifications are sent automatically..."
}
```

**Important Notes:**
- PhonePe OAuth API doesn't have separate pre-debit endpoint
- Pre-debit SMS is sent automatically by bank when you trigger redemption
- This API only verifies subscription is ACTIVE and ready
- Actual pre-debit notification comes when you call "Trigger Auto-Debit"

---

#### 🔹 Test 4: Trigger Real Payment

**Endpoint:** `4. Trigger Auto-Debit (Real Payment)`

**⚠️ WARNING: This charges real money!**

**Request:**
```
POST /phonepe/trigger-autodebit
Content-Type: application/x-www-form-urlencoded

merchantSubscriptionId=MS1234567890
amount=1
```

**Expected Response:**
```json
{
  "success": true,
  "message": "Auto-debit triggered successfully!...",
  "merchant_order_id": "MO_REDEEM_1234567890",
  "phonepe_order_id": "OMO_REDEEM_1234567890",
  "state": "COMPLETED",
  "data": {
    "orderId": "OMO_REDEEM_1234567890",
    "transactionId": "TXN_1234567890",
    "state": "COMPLETED"
  }
}
```

**What Happens:**
1. Verifies subscription is ACTIVE
2. Creates redemption request with PhonePe
3. Bank sends pre-debit SMS to user
4. Payment is processed automatically
5. Money is debited from user's account
6. Database records updated:
   - `autopay_count` incremented
   - `last_autopay_at` updated
   - `next_autopay_at` set to +1 month
   - Notification created

**Prerequisites:**
- Subscription must be ACTIVE
- User must have approved mandate
- Sufficient balance in account

---

#### 🔹 Test 5: Simulate Payment (No Real Money)

**Endpoint:** `5. Simulate Auto-Debit (Testing Only)`

**Request:**
```
POST /phonepe/simulate-autodebit
Content-Type: application/x-www-form-urlencoded

merchantSubscriptionId=MS1234567890
amount=1
```

**Expected Response:**
```json
{
  "success": true,
  "message": "✅ Auto-debit simulated successfully!",
  "data": {
    "autopay_count": 1,
    "last_payment": "17 Feb 2026, 10:30 AM",
    "next_payment": "17 Mar 2026, 10:30 AM",
    "amount": "₹1.00"
  },
  "note": "⚠️ This is a simulation. In production, PhonePe automatically debits based on schedule."
}
```

**What Happens:**
1. No real payment is made
2. Database records updated as if payment succeeded
3. Useful for testing UI and flow
4. Safe for development/testing

**Use Cases:**
- Testing without real money
- Demonstrating the flow
- UI/UX testing
- Development environment

---

#### 🔹 Test 6: Get History

**Endpoint:** `6. Get Transaction History`

**Request:**
```
GET /phonepe/get-history
```

**Expected Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "merchant_order_id": "MO1234567890",
      "merchant_subscription_id": "MS1234567890",
      "phonepe_order_id": "OMO1234567890",
      "upi_id": "vrajsurani606@okaxis",
      "mobile": "9724085965",
      "amount": 1,
      "status": "PENDING",
      "subscription_state": "ACTIVE",
      "is_autopay_active": true,
      "autopay_count": 0,
      "predebit_sent": false,
      "created_at": "2026-02-17T10:30:00.000000Z",
      "updated_at": "2026-02-17T10:30:00.000000Z"
    }
  ]
}
```

**Use Cases:**
- Monitor all transactions
- Check subscription states
- Verify payment counts
- Audit trail

---

## 📊 Database Tables

### 1. phonepe_autopay_test_history
Legacy table for testing, stores all test transactions.

### 2. phonepe_transactions
Main transactions table with fields:
- `merchant_order_id`
- `merchant_subscription_id`
- `phonepe_order_id`
- `phonepe_transaction_id`
- `transaction_type`
- `upi_id`
- `mobile`
- `amount`
- `status`
- `payment_state`
- `is_autopay_active`
- `autopay_count`
- `last_autopay_at`
- `next_autopay_at`
- `request_payload`
- `response_data`

### 3. phonepe_notifications
Stores all notification events:
- `notification_type` (SUBSCRIPTION_SETUP, STATUS_CHECK, PRE_DEBIT_INFO, PAYMENT_SUCCESS, etc.)
- `event_type`
- `amount`
- `status`
- `payment_method`
- `is_processed`
- `processed_at`

---

## 🔐 Authentication Details

### OAuth Credentials (Production)

```php
Client ID: SU2512031928441979485878
Client Secret: 04652cf1-d98d-4f48-8ae8-0ecf60fac76f
Client Version: 1
Merchant User ID: M22EOXLUSO1LA
```

### Token Management

- Tokens are cached for 55 minutes (5 min before expiry)
- Automatic token refresh on expiry
- Cache key: `phonepe_access_token`

### API Endpoints

**OAuth Token:**
```
POST https://api.phonepe.com/apis/identity-manager/v1/oauth/token
```

**Subscription Setup:**
```
POST https://api.phonepe.com/apis/pg/subscriptions/v2/setup
```

**Subscription Status:**
```
GET https://api.phonepe.com/apis/pg/subscriptions/v2/{subscriptionId}/status?details=true
```

**Subscription Redemption:**
```
POST https://api.phonepe.com/apis/pg/subscriptions/v2/redeem
```

---

## 🧪 Complete Testing Workflow

### Scenario 1: Full AutoPay Flow

1. **Create Subscription**
   ```
   POST /phonepe/send-payment-request
   upi_id=your@upi
   amount=1
   mobile=9999999999
   ```
   → Save `merchant_subscription_id`

2. **Approve on Phone**
   - Check your phone for UPI notification
   - Approve the mandate

3. **Verify Status**
   ```
   POST /phonepe/check-subscription-status
   merchantSubscriptionId=MS...
   ```
   → Should return `state: "ACTIVE"`

4. **Check Pre-Debit Readiness**
   ```
   POST /phonepe/send-predebit
   merchantSubscriptionId=MS...
   amount=1
   ```
   → Confirms subscription is ready

5. **Trigger Payment**
   ```
   POST /phonepe/trigger-autodebit
   merchantSubscriptionId=MS...
   amount=1
   ```
   → Real payment processed

6. **Verify History**
   ```
   GET /phonepe/get-history
   ```
   → Check autopay_count increased

---

### Scenario 2: Testing Without Real Money

1. **Create Subscription** (same as above)

2. **Approve on Phone** (same as above)

3. **Verify Status** (same as above)

4. **Simulate Payment** (instead of real payment)
   ```
   POST /phonepe/simulate-autodebit
   merchantSubscriptionId=MS...
   amount=1
   ```
   → No real money charged

5. **Verify History**
   ```
   GET /phonepe/get-history
   ```
   → Check simulated payment recorded

---

## 🐛 Troubleshooting

### Issue: "OAuth Token Generation Failed"

**Solution:**
- Check credentials in controller
- Verify internet connection
- Check PhonePe API status

### Issue: "Subscription must be ACTIVE"

**Solution:**
- User hasn't approved mandate yet
- Check status with status API
- Wait for user approval

### Issue: "CSRF token mismatch"

**Solution:**
- Get fresh CSRF token from browser
- Update Postman environment variable
- Ensure you're logged in

### Issue: "Unauthorized" or "403 Forbidden"

**Solution:**
- Login as Admin user
- Check IsAdmin middleware
- Verify session is active

---

## 📱 Testing on Real Device

### Prerequisites
1. Real UPI ID (e.g., yourname@okaxis)
2. UPI app installed on phone
3. Active bank account linked to UPI

### Steps
1. Use your real UPI ID in request
2. Check phone for notification
3. Approve mandate in UPI app
4. Test with small amount (₹1)
5. Verify payment in bank statement

---

## 🎯 Best Practices

1. **Always test with ₹1 first**
2. **Use simulate for development**
3. **Check status before triggering debit**
4. **Monitor database tables**
5. **Keep track of subscription IDs**
6. **Review logs for errors**
7. **Test on staging before production**

---

## 📞 Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Review PhonePe documentation
- Contact PhonePe support for API issues

---

## ✅ Checklist

Before going live:
- [ ] Test complete flow with real UPI
- [ ] Verify all database tables
- [ ] Check webhook handling
- [ ] Test error scenarios
- [ ] Review security measures
- [ ] Set up monitoring
- [ ] Document production credentials
- [ ] Train support team

---

**Last Updated:** February 17, 2026
**Version:** 1.0
**Status:** Production Ready ✅
