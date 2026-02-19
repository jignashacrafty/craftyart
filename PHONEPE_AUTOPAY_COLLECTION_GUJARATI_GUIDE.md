# PhonePe AutoPay API Collection - સંપૂર્ણ માર્ગદર્શિકા

## 📦 Collection વિશે

આ collection **ORDER_USER_API_POSTMAN_COLLECTION_COMPLETE.json** જેવી જ રીતે બનાવેલ છે, જેમાં:

✅ **Proper Response Examples** - દરેક API માટે success અને error responses
✅ **Auto-Save Variables** - merchant_subscription_id અને merchant_order_id automatically save થાય છે
✅ **Detailed Descriptions** - દરેક endpoint નું સંપૂર્ણ documentation
✅ **Multiple Response Scenarios** - વિવિધ પરિસ્થિતિઓ માટે example responses

## 🎯 Collection Structure

### 1. PhonePe AutoPay APIs (મુખ્ય APIs)

#### 1.1 Setup AutoPay Subscription
**Endpoint:** `POST /api/phonepe/autopay/setup`

**Request Body:**
```json
{
    "user_id": "test_user_123",
    "plan_id": "plan_monthly_99",
    "amount": 1,
    "upi": "vrajsurani606@okaxis",
    "target_app": "com.phonepe.app"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Subscription setup initiated successfully",
    "data": {
        "merchant_order_id": "MO_SETUP_65d8f1234567890",
        "merchant_subscription_id": "MS_65d8f1234567891",
        "phonepe_order_id": "PP_ORD_123456789",
        "redirect_url": "https://mercury-uat.phonepe.com/transact/pg?token=abc123xyz",
        "state": "PENDING",
        "expire_at": 1708345200000
    }
}
```

**Error Responses:**
- **404 - User Not Found:** જ્યારે user_id invalid હોય
- **400 - Authorization Failed:** જ્યારે PhonePe credentials invalid હોય

**શું થાય છે:**
1. Database માં Order create થાય છે
2. OAuth token automatically generate થાય છે
3. PhonePe ને subscription setup request મોકલાય છે
4. User ને payment માટે redirect URL મળે છે
5. User પોતાના phone પર mandate approve કરે છે

---

#### 1.2 Get Subscription Status
**Endpoint:** `GET /api/phonepe/autopay/status/{merchant_subscription_id}`

**Success Response (200) - Active:**
```json
{
    "success": true,
    "data": {
        "local_status": "ACTIVE",
        "phonepe_status": "ACTIVE",
        "details": {
            "state": "ACTIVE",
            "merchantSubscriptionId": "MS_65d8f1234567891",
            "subscriptionId": "PP_SUB_123456789",
            "amount": 100,
            "currency": "INR",
            "frequency": "Monthly",
            "startDate": "2026-02-18",
            "nextBillingDate": "2026-03-18",
            "expireAt": 1739836800000
        }
    }
}
```

**Success Response (200) - Pending:**
```json
{
    "success": true,
    "data": {
        "local_status": "PENDING",
        "phonepe_status": "PENDING",
        "details": {
            "state": "PENDING",
            "merchantSubscriptionId": "MS_65d8f1234567891",
            "message": "User approval pending"
        }
    }
}
```

**Subscription States:**
- `PENDING` - User એ હજુ approve નથી કર્યું
- `ACTIVE` - ✅ Subscription active છે અને ready છે
- `COMPLETED` - Subscription પૂર્ણ થઈ ગયું
- `FAILED` - Setup fail થયું
- `CANCELLED` - User એ cancel કર્યું

**ક્યારે use કરવું:**
- User એ mandate approve કર્યું છે કે નહીં તે verify કરવા
- Redemption trigger કરતા પહેલા check કરવા
- Subscription ની health monitor કરવા

---

#### 1.3 Trigger Manual Redemption
**Endpoint:** `POST /api/phonepe/autopay/redeem`

**Request Body:**
```json
{
    "merchant_subscription_id": "MS_65d8f1234567891"
}
```

**Success Response (200) - Production માં:**
```json
{
    "success": true,
    "message": "Manual redemption triggered",
    "data": {
        "merchant_order_id": "MO_MANUAL_65d8f9876543210",
        "phonepe_order_id": "PP_ORD_987654321"
    }
}
```

**Sandbox Limitation Response (400):**
```json
{
    "success": false,
    "message": "PhonePe Subscription Redemption API Not Available in Sandbox",
    "error": "The subscription redemption/auto-debit API is only available in production environment. In sandbox, you can only test subscription setup.",
    "details": {
        "subscription_id": 123,
        "merchant_subscription_id": "MS_65d8f1234567891",
        "amount": 1,
        "next_billing_date": "2026-03-18",
        "http_code": 204,
        "note": "To test auto-debit, you need production credentials and a live UPI mandate."
    }
}
```

**⚠️ મહત્વપૂર્ણ નોંધો:**

1. **Sandbox Limitation:**
   - PhonePe Sandbox માં redemption API કામ કરતું નથી
   - તમને "AUTHORIZATION_FAILED" અથવા 204 No Content મળશે
   - આ sandbox માં NORMAL છે

2. **Production Only:**
   - Redemption API માત્ર production credentials સાથે કામ કરે છે
   - Live UPI mandate જરૂરી છે જે user એ approve કર્યું હોય
   - User ના account માંથી real money debit થશે

3. **Testing in Sandbox:**
   - તમે માત્ર subscription SETUP test કરી શકો છો
   - Actual auto-debit test કરી શકાતું નથી
   - Full testing માટે production use કરો

**Prerequisites:**
- Subscription ACTIVE હોવું જોઈએ
- User એ mandate approve કર્યું હોવું જોઈએ
- Same day માં બે વાર trigger ન કરી શકાય

**Error Responses:**
- **400 - Already Processed Today:** આજે પહેલેથી trigger થઈ ગયું છે
- **404 - Subscription Not Found:** Active subscription નથી મળ્યું

---

#### 1.4 Cancel Subscription
**Endpoint:** `POST /api/phonepe/autopay/cancel`

**Request Body:**
```json
{
    "merchant_subscription_id": "MS_65d8f1234567891"
}
```

**Success Response (200):**
```json
{
    "success": true,
    "message": "Subscription cancelled successfully"
}
```

**Error Response (404):**
```json
{
    "success": false,
    "message": "Subscription not found"
}
```

**શું થાય છે:**
1. PhonePe ને cancellation request મોકલાય છે
2. Database માં status CANCELLED થાય છે
3. User નું mandate revoke થાય છે
4. હવે કોઈ auto-debit થશે નહીં

**Use Cases:**
- User subscription stop કરવા માંગે છે
- Refund scenario
- Plan downgrade
- Account closure

---

### 2. Web Routes (Browser માટે)

#### 2.1 Simple Payment Test Page
**Endpoint:** `GET /phonepe/simple-payment-test`

**⚠️ આ WEB route છે, API નથી!**

**Requirements:**
- Admin તરીકે login હોવું જોઈએ
- માત્ર browser માં open કરવું
- CSRF token Laravel automatically handle કરે છે

**Features:**
- Interactive payment request form
- Real-time transaction history
- દરેક transaction માટે action buttons
- IDs copy કરવાની functionality

---

## 🔧 Collection Variables

Collection માં આ variables automatically save થાય છે:

| Variable | Description | Auto-Saved From |
|----------|-------------|-----------------|
| `base_url` | Main website URL | Manual |
| `api_base_url` | API base URL | Manual |
| `merchant_subscription_id` | Subscription ID | Setup API response |
| `merchant_order_id` | Order ID | Setup/Redeem API response |

---

## 📝 Auto-Save Script

Collection માં global test script છે જે automatically variables save કરે છે:

```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data) {
        if (jsonData.data.merchant_subscription_id) {
            pm.collectionVariables.set('merchant_subscription_id', jsonData.data.merchant_subscription_id);
            console.log('✅ Saved merchant_subscription_id:', jsonData.data.merchant_subscription_id);
        }
        if (jsonData.data.merchant_order_id) {
            pm.collectionVariables.set('merchant_order_id', jsonData.data.merchant_order_id);
            console.log('✅ Saved merchant_order_id:', jsonData.data.merchant_order_id);
        }
    }
}
```

---

## 🚀 કેવી રીતે Use કરવું

### Step 1: Collection Import કરો
1. Postman open કરો
2. Import button click કરો
3. `PHONEPE_AUTOPAY_API_COLLECTION_COMPLETE.json` select કરો

### Step 2: Variables Set કરો
1. Collection પર right-click કરો
2. "Edit" select કરો
3. "Variables" tab માં જાઓ
4. `base_url` અને `api_base_url` update કરો

### Step 3: APIs Test કરો

**Testing Flow:**
1. **Setup Subscription** → merchant_subscription_id automatically save થશે
2. **Get Status** → subscription status check કરો
3. **Trigger Redemption** → manual auto-debit trigger કરો (production માં)
4. **Cancel Subscription** → જરૂર પડે તો cancel કરો

---

## 🔍 Response Examples સાથે Comparison

### ORDER_USER Collection જેવું જ Structure:

✅ **Multiple Response Scenarios** - દરેક API માટે success અને error cases
✅ **Proper HTTP Status Codes** - 200, 400, 404, 500
✅ **Detailed Error Messages** - સ્પષ્ટ error descriptions
✅ **Auto-Save Functionality** - Important IDs automatically save
✅ **Gujarati + English Documentation** - બંને ભાષામાં સમજૂતી

---

## 📊 Project Functionality Check

આ collection તમારા project ની આ functionality test કરે છે:

### ✅ PhonePeAutoPayController.php
- `setupSubscription()` - Subscription setup
- `getSubscriptionStatus()` - Status check
- `triggerManualRedemption()` - Manual redemption
- `cancelSubscription()` - Subscription cancellation

### ✅ Database Tables
- `phonepe_subscriptions` - Subscription records
- `phonepe_autopay_transactions` - Transaction records
- `orders` - Order records

### ✅ PhonePeTokenService
- OAuth token generation
- Automatic token refresh
- Token caching

### ✅ API Routes (routes/api.php)
```php
Route::prefix('phonepe/autopay')->group(function () {
    Route::post('setup', [PhonePeAutoPayController::class, 'setupSubscription']);
    Route::get('status/{merchantSubscriptionId}', [PhonePeAutoPayController::class, 'getSubscriptionStatus']);
    Route::post('redeem', [PhonePeAutoPayController::class, 'triggerManualRedemption']);
    Route::post('cancel', [PhonePeAutoPayController::class, 'cancelSubscription']);
});
```

---

## 🎉 Summary

આ collection ORDER_USER collection જેવી જ રીતે બનાવેલ છે:
- ✅ Proper encrypted structure
- ✅ Complete response examples
- ✅ Auto-save functionality
- ✅ Detailed documentation
- ✅ Multiple scenarios covered
- ✅ Production-ready testing

હવે તમે આ collection use કરીને તમારા PhonePe AutoPay functionality ને સંપૂર્ણ રીતે test કરી શકો છો! 🚀
