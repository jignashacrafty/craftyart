# PhonePe Simple Payment API - Quick Reference

## 🚀 Base URL
```
http://localhost/git_jignasha/craftyart/public
```

## 🔑 Authentication
All endpoints require:
- Admin login session
- CSRF token in header: `X-CSRF-TOKEN`

---

## 📡 API Endpoints

### 1️⃣ Send Payment Request
**Create new AutoPay subscription**

```http
POST /phonepe/send-payment-request
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {token}

upi_id=vrajsurani606@okaxis
amount=1
mobile=9724085965
```

**Response:**
```json
{
  "success": true,
  "message": "Payment request sent successfully!",
  "data": {
    "merchant_order_id": "MO...",
    "merchant_subscription_id": "MS...",
    "order_id": "OMO...",
    "state": "PENDING"
  }
}
```

---

### 2️⃣ Check Status
**Get subscription current state**

```http
POST /phonepe/check-subscription-status
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {token}

merchantSubscriptionId=MS...
```

**Response:**
```json
{
  "success": true,
  "data": {
    "state": "ACTIVE",
    "subscriptionId": "SUB...",
    "merchantSubscriptionId": "MS..."
  }
}
```

**States:** `PENDING` | `ACTIVE` | `COMPLETED` | `FAILED` | `CANCELLED`

---

### 3️⃣ Pre-Debit Check
**Verify subscription ready for payment**

```http
POST /phonepe/send-predebit
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {token}

merchantSubscriptionId=MS...
amount=1
```

**Response:**
```json
{
  "success": true,
  "message": "✅ Subscription is ACTIVE and ready!",
  "subscription_state": "ACTIVE",
  "note": "Pre-debit SMS sent by bank when redemption triggered"
}
```

---

### 4️⃣ Trigger Auto-Debit
**⚠️ Real payment - charges money!**

```http
POST /phonepe/trigger-autodebit
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {token}

merchantSubscriptionId=MS...
amount=1
```

**Response:**
```json
{
  "success": true,
  "message": "Auto-debit triggered successfully!",
  "merchant_order_id": "MO_REDEEM_...",
  "phonepe_order_id": "OMO_...",
  "state": "COMPLETED"
}
```

---

### 5️⃣ Simulate Auto-Debit
**🧪 Test only - no real payment**

```http
POST /phonepe/simulate-autodebit
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: {token}

merchantSubscriptionId=MS...
amount=1
```

**Response:**
```json
{
  "success": true,
  "message": "✅ Auto-debit simulated successfully!",
  "data": {
    "autopay_count": 1,
    "last_payment": "17 Feb 2026, 10:30 AM",
    "next_payment": "17 Mar 2026, 10:30 AM"
  }
}
```

---

### 6️⃣ Get History
**Retrieve all transactions**

```http
GET /phonepe/get-history
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "merchant_subscription_id": "MS...",
      "phonepe_order_id": "OMO...",
      "upi_id": "user@okaxis",
      "amount": 1,
      "subscription_state": "ACTIVE",
      "is_autopay_active": true,
      "autopay_count": 0,
      "created_at": "2026-02-17T10:30:00Z"
    }
  ]
}
```

---

### 7️⃣ Web Interface
**Browser-based testing page**

```http
GET /phonepe/simple-payment-test
```

Opens interactive test page with:
- Payment request form
- Transaction history table
- Action buttons for each transaction

---

## 🔄 Typical Flow

```
1. Send Payment Request
   ↓
2. User Approves on Phone
   ↓
3. Check Status (verify ACTIVE)
   ↓
4. Pre-Debit Check (optional)
   ↓
5. Trigger Auto-Debit (real payment)
   OR
   Simulate Auto-Debit (testing)
   ↓
6. Get History (verify)
```

---

## 📋 Quick Copy-Paste Examples

### cURL: Send Payment Request
```bash
curl -X POST "http://localhost/git_jignasha/craftyart/public/phonepe/send-payment-request" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "upi_id=vrajsurani606@okaxis" \
  -d "amount=1" \
  -d "mobile=9724085965"
```

### cURL: Check Status
```bash
curl -X POST "http://localhost/git_jignasha/craftyart/public/phonepe/check-subscription-status" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "merchantSubscriptionId=MS1234567890"
```

### cURL: Trigger Debit
```bash
curl -X POST "http://localhost/git_jignasha/craftyart/public/phonepe/trigger-autodebit" \
  -H "X-CSRF-TOKEN: YOUR_TOKEN" \
  -H "Cookie: laravel_session=YOUR_SESSION" \
  -d "merchantSubscriptionId=MS1234567890" \
  -d "amount=1"
```

### cURL: Get History
```bash
curl -X GET "http://localhost/git_jignasha/craftyart/public/phonepe/get-history" \
  -H "Cookie: laravel_session=YOUR_SESSION"
```

---

## 🎯 Response Codes

| Code | Meaning |
|------|---------|
| 200 | Success |
| 400 | Bad Request (invalid parameters) |
| 401 | Unauthorized (not logged in) |
| 403 | Forbidden (not admin) |
| 404 | Not Found |
| 500 | Server Error |

---

## 🔐 OAuth Details

**Token Endpoint:**
```
POST https://api.phonepe.com/apis/identity-manager/v1/oauth/token
```

**Credentials:**
- Client ID: `SU2512031928441979485878`
- Client Secret: `04652cf1-d98d-4f48-8ae8-0ecf60fac76f`
- Grant Type: `client_credentials`

**Token Cache:** 55 minutes

---

## 📊 Database Tables

### phonepe_transactions
Main table for all transactions

**Key Fields:**
- `merchant_subscription_id` - Your subscription ID
- `phonepe_order_id` - PhonePe's order ID
- `status` - Current status
- `is_autopay_active` - Boolean
- `autopay_count` - Number of successful debits

### phonepe_notifications
All notification events

**Key Fields:**
- `notification_type` - Event type
- `status` - Event status
- `is_processed` - Processing status

---

## ⚡ Quick Tips

1. **Always save `merchant_subscription_id`** from first response
2. **Check status before triggering debit**
3. **Use simulate for testing**
4. **Start with ₹1 amount**
5. **Monitor logs:** `storage/logs/laravel.log`

---

## 🐛 Common Errors

### "OAuth Token Generation Failed"
→ Check credentials and internet connection

### "Subscription must be ACTIVE"
→ User hasn't approved mandate yet

### "CSRF token mismatch"
→ Get fresh token from browser

### "Unauthorized"
→ Login as admin first

---

## 📱 Test UPI IDs

```
vrajsurani606@okaxis
yourname@paytm
yourname@ybl
yourname@oksbi
```

---

## 🎨 Postman Collection

Import: `PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json`

**Variables to set:**
- `base_url`: Your application URL
- `csrf_token`: Get from browser

---

## 📞 Support Resources

- **Laravel Logs:** `storage/logs/laravel.log`
- **PhonePe Docs:** https://developer.phonepe.com/
- **Test Page:** `/phonepe/simple-payment-test`

---

**Version:** 1.0  
**Last Updated:** February 17, 2026  
**Status:** ✅ Production Ready
