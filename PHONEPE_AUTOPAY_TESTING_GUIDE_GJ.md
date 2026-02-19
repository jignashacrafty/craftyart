# PhonePe AutoPay API Testing Guide (Gujarati)

## ✅ સમસ્યા ઠીક થઈ!

**પહેલાની સમસ્યા:** CSRF token mismatch error આવતી હતી

**ઉકેલ:** હવે proper API routes use કરીએ છીએ જેમાં CSRF token ની જરૂર નથી!

---

## 🎯 નવું Postman Collection

**File:** `PHONEPE_AUTOPAY_API_COLLECTION.json`

### ✨ Features:

1. ✅ **NO CSRF TOKEN REQUIRED** - આ API routes છે!
2. ✅ Main `PhonePeAutoPayController` use કરે છે
3. ✅ Automatic OAuth token management
4. ✅ Production credentials સાથે
5. ✅ 4 API endpoints ready

---

## 📡 API Endpoints

### 1️⃣ Setup AutoPay Subscription

```http
POST /api/phonepe/autopay/setup
Content-Type: application/json

{
    "user_id": "test_user_123",
    "plan_id": "plan_monthly_99",
    "amount": 1,
    "upi": "vrajsurani606@okaxis",
    "target_app": "com.phonepe.app"
}
```

**શું થશે:**
- Order database માં create થશે
- OAuth token automatic generate થશે
- PhonePe ને subscription setup request જશે
- Redirect URL મળશે user ને payment માટે
- `merchant_subscription_id` save કરો!

**Response:**
```json
{
    "success": true,
    "message": "Subscription setup initiated successfully",
    "data": {
        "merchant_order_id": "MO_SETUP_...",
        "merchant_subscription_id": "MS_...",
        "phonepe_order_id": "OMO...",
        "redirect_url": "https://...",
        "state": "PENDING"
    }
}
```

---

### 2️⃣ Get Subscription Status

```http
GET /api/phonepe/autopay/status/{merchant_subscription_id}
```

**Example:**
```
GET /api/phonepe/autopay/status/MS_65d8f9a1234567890
```

**Response:**
```json
{
    "success": true,
    "data": {
        "local_status": "ACTIVE",
        "phonepe_status": "ACTIVE",
        "details": {
            "state": "ACTIVE",
            "subscriptionId": "SUB...",
            "amount": 100
        }
    }
}
```

**States:**
- `PENDING` - હજુ approve નથી
- `ACTIVE` - ✅ Ready છે
- `COMPLETED` - પૂર્ણ થયું
- `FAILED` - નિષ્ફળ
- `CANCELLED` - રદ કર્યું

---

### 3️⃣ Trigger Manual Redemption

```http
POST /api/phonepe/autopay/redeem
Content-Type: application/json

{
    "merchant_subscription_id": "MS_..."
}
```

**⚠️ મહત્વપૂર્ણ નોંધ:**

**Sandbox માં:**
- આ API કામ નહીં કરે
- "AUTHORIZATION_FAILED" error આવશે
- આ NORMAL છે sandbox માં

**Production માં:**
- આ API કામ કરશે
- Real payment થશે
- પૈસા કપાશે

**Response (Sandbox):**
```json
{
    "success": false,
    "message": "PhonePe Subscription Redemption API Not Available in Sandbox",
    "error": "The subscription redemption/auto-debit API is only available in production environment...",
    "details": {
        "subscription_id": 1,
        "merchant_subscription_id": "MS_...",
        "amount": 1,
        "note": "To test auto-debit, you need production credentials..."
    }
}
```

---

### 4️⃣ Cancel Subscription

```http
POST /api/phonepe/autopay/cancel
Content-Type: application/json

{
    "merchant_subscription_id": "MS_..."
}
```

**શું થશે:**
- PhonePe ને cancellation request જશે
- Database માં status CANCELLED થશે
- User નું mandate revoke થશે
- વધુ auto-debit નહીં થાય

---

## 🚀 Postman માં કેવી રીતે Test કરવું?

### Step 1: Collection Import કરો

1. Postman ખોલો
2. "Import" button click કરો
3. `PHONEPE_AUTOPAY_API_COLLECTION.json` select કરો
4. Import થઈ જશે

### Step 2: Variables Set કરો

Postman માં આ variables set કરો:

```
base_url = http://localhost/git_jignasha/craftyart/public
merchant_subscription_id = (પહેલા response માંથી મળશે)
```

### Step 3: APIs Test કરો

**Test 1: Setup Subscription**

1. "1. Setup AutoPay Subscription" request ખોલો
2. Body માં details check કરો:
   ```json
   {
       "user_id": "test_user_123",
       "plan_id": "plan_monthly_99",
       "amount": 1,
       "upi": "vrajsurani606@okaxis"
   }
   ```
3. "Send" button click કરો
4. Response માંથી `merchant_subscription_id` copy કરો
5. Postman variable માં save કરો

**Test 2: Check Status**

1. "2. Get Subscription Status" request ખોલો
2. URL માં `{{merchant_subscription_id}}` automatic replace થશે
3. "Send" button click કરો
4. Status check કરો - PENDING હશે

**Test 3: Try Redemption (Sandbox માં fail થશે)**

1. "3. Trigger Manual Redemption" request ખોલો
2. Body માં subscription ID check કરો
3. "Send" button click કરો
4. Error message આવશે કે sandbox માં available નથી
5. આ NORMAL છે!

**Test 4: Cancel Subscription**

1. "4. Cancel Subscription" request ખોલો
2. "Send" button click કરો
3. Success message આવશે

---

## 🎨 Web Interface પણ છે!

**URL:** `http://localhost/git_jignasha/craftyart/public/phonepe/simple-payment-test`

**⚠️ આ WEB route છે, API નહીં!**

**Features:**
- Interactive form
- Transaction history
- Action buttons
- Copy IDs functionality

**Requirements:**
- Admin login જરૂરી
- Browser માં ખોલવું
- CSRF token automatic handle થાય છે

---

## 🔑 મુખ્ય તફાવત

### API Routes (નવું - ✅ Recommended)

```
POST /api/phonepe/autopay/setup
GET  /api/phonepe/autopay/status/{id}
POST /api/phonepe/autopay/redeem
POST /api/phonepe/autopay/cancel
```

**ફાયદા:**
- ✅ NO CSRF token required
- ✅ Postman માં સીધું test કરી શકાય
- ✅ Mobile apps માટે use કરી શકાય
- ✅ Third-party integration માટે સારું

### Web Routes (જૂનું)

```
POST /phonepe/send-payment-request
POST /phonepe/check-subscription-status
POST /phonepe/trigger-autodebit
```

**Issues:**
- ❌ CSRF token જરૂરી
- ❌ Browser session જરૂરી
- ❌ Postman માં મુશ્કેલી
- ✅ Web interface માટે સારું

---

## 📊 Database Tables

### phonepe_subscriptions
Main subscription table

**Important Fields:**
- `merchant_subscription_id` - તમારી subscription ID
- `phonepe_subscription_id` - PhonePe ની ID
- `status` - Current status
- `subscription_status` - PhonePe status
- `amount` - Amount
- `next_billing_date` - Next payment date

### phonepe_autopay_transactions
Transaction history

**Important Fields:**
- `subscription_id` - Link to subscription
- `merchant_order_id` - Order ID
- `amount` - Transaction amount
- `transaction_type` - Type (manual/auto)
- `status` - Transaction status

### orders
Main orders table

**Important Fields:**
- `user_id` - User ID
- `plan_id` - Plan ID
- `amount` - Amount
- `status` - Order status
- `razorpay_order_id` - Contains PHONEPE_ prefix

---

## 🐛 Troubleshooting

### Error: "CSRF token mismatch"

**કારણ:** તમે web route use કરી રહ્યા છો Postman માં

**ઉકેલ:** API routes use કરો:
```
❌ POST /phonepe/send-payment-request
✅ POST /api/phonepe/autopay/setup
```

### Error: "AUTHORIZATION_FAILED" (Redemption માં)

**કારણ:** Sandbox માં redemption API available નથી

**ઉકેલ:** આ NORMAL છે! Production માં કામ કરશે

### Error: "Subscription not found"

**કારણ:** Wrong subscription ID

**ઉકેલ:** Setup response માંથી correct ID copy કરો

### Error: "User not found"

**કારણ:** Invalid user_id

**ઉકેલ:** Valid user_id use કરો database માંથી

---

## ✅ Testing Checklist

### Postman Testing:

- [ ] Collection import કર્યું
- [ ] Variables set કર્યા
- [ ] Setup API test કર્યું
- [ ] merchant_subscription_id save કર્યું
- [ ] Status API test કર્યું
- [ ] Redemption API try કર્યું (sandbox માં fail થશે)
- [ ] Cancel API test કર્યું

### Web Interface Testing:

- [ ] Admin login કર્યું
- [ ] Test page ખોલ્યું
- [ ] Payment request મોકલ્યું
- [ ] Transaction history જોયું
- [ ] Action buttons try કર્યા

### Database Verification:

- [ ] `phonepe_subscriptions` table check કર્યું
- [ ] `orders` table check કર્યું
- [ ] Status updates verify કર્યા

---

## 🎯 Production માટે

### Production માં test કરવા માટે:

1. **Production Credentials:**
   ```
   Client ID: SU2512031928441979485878
   Client Secret: 04652cf1-d98d-4f48-8ae8-0ecf60fac76f
   Merchant User ID: M22EOXLUSO1LA
   ```

2. **Environment:**
   - PaymentConfiguration માં environment = "production" set કરો

3. **Real UPI:**
   - Real UPI ID use કરો
   - Real phone number use કરો
   - Real bank account linked હોવું જોઈએ

4. **Testing:**
   - ₹1 થી શરૂ કરો
   - Phone પર notification આવશે
   - Approve કરો mandate
   - Redemption API કામ કરશે
   - Real payment થશે

---

## 📞 Help

### Files:
- **API Collection:** `PHONEPE_AUTOPAY_API_COLLECTION.json`
- **Old Collection:** `PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json` (web routes)
- **Testing Guide:** `PHONEPE_SIMPLE_PAYMENT_TESTING_GUIDE.md`
- **Quick Reference:** `PHONEPE_API_QUICK_REFERENCE.md`

### Logs:
```
storage/logs/laravel.log
```

### Database:
```sql
SELECT * FROM phonepe_subscriptions ORDER BY created_at DESC LIMIT 10;
SELECT * FROM phonepe_autopay_transactions ORDER BY created_at DESC LIMIT 10;
SELECT * FROM orders WHERE razorpay_order_id LIKE 'PHONEPE_%' ORDER BY created_at DESC LIMIT 10;
```

---

## 🎉 Summary

✅ **Fixed:** CSRF token issue  
✅ **Added:** Proper API routes  
✅ **Created:** New Postman collection  
✅ **Updated:** routes/api.php  
✅ **Ready:** Production testing  

**હવે Postman માં સીધું test કરી શકો છો - NO CSRF TOKEN NEEDED!**

---

**Last Updated:** 17 February 2026  
**Version:** 2.0  
**Status:** ✅ Fixed & Ready
