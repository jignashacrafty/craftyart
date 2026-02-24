# PhonePe ACTIVATION_IN_PROGRESS Status - સમજૂતી (Gujarati)

## તમારો પ્રશ્ન
તમે AutoPay decline કર્યું છે, પણ API હજુ પણ `ACTIVATION_IN_PROGRESS` બતાવે છે.

## સમસ્યા શું છે?

### PhonePe Status Update Delay ⏰

**મહત્વનું:** PhonePe તરત જ status update નથી કરતું!

```
1. તમે AutoPay request મોકલો
   → PhonePe Status: ACTIVATION_IN_PROGRESS

2. તમે ફોન પર decline click કરો
   → PhonePe Status: હજુ પણ ACTIVATION_IN_PROGRESS (!)

3. થોડી વાર રાહ જુઓ (5-10 મિનિટ)
   → PhonePe Status: FAILED અથવા CANCELLED

4. Status check API call કરો
   → હવે FAILED બતાવશે
```

### કેમ આવું થાય છે?

PhonePe ની system માં status update થવામાં સમય લાગે છે:

1. **તમારો Action:** Decline button click
2. **Bank Processing:** Bank ને notification મોકલે છે
3. **PhonePe Update:** PhonePe database update કરે છે (5-10 મિનિટ)
4. **API Response:** Status check કરતા નવું status મળે છે

## હાલની API Response

### તમે જે જોઈ રહ્યા છો:
```json
{
    "statusCode": 200,
    "success": true,
    "msg": "Subscription status retrieved",
    "data": {
        "state": "ACTIVATION_IN_PROGRESS",
        "phonepe_state": "ACTIVATION_IN_PROGRESS",
        "subscription_id": "OMS2602231500342493490289W",
        "merchant_subscription_id": "MS_699c1e39bec881771839033",
        "is_active": false,
        "details": {...}
    }
}
```

### હવે API શું return કરશે:

```json
{
    "statusCode": 200,
    "success": true,
    "msg": "Subscription status retrieved",
    "data": {
        "state": "PENDING",                          // ← Mapped status
        "phonepe_state": "ACTIVATION_IN_PROGRESS",   // ← Original PhonePe status
        "subscription_id": "OMS2602231500342493490289W",
        "merchant_subscription_id": "MS_699c1e39bec881771839033",
        "is_active": false,
        "details": {...}
    }
}
```

## Status Mapping

### API હવે આ રીતે map કરે છે:

| PhonePe Status | API Returns | Display | Meaning |
|----------------|-------------|---------|---------|
| PENDING | PENDING | PENDING ⏳ | User hasn't seen notification |
| ACTIVATION_IN_PROGRESS | PENDING | PENDING ⏳ | User is reviewing |
| ACTIVE | ACTIVE | ACTIVE ✅ | User approved |
| COMPLETED | COMPLETED | COMPLETED ✔️ | Payment done |
| FAILED | FAILED | FAILED ❌ | User declined |
| CANCELLED | CANCELLED | CANCELLED 🚫 | Cancelled |
| EXPIRED | EXPIRED | EXPIRED ⏰ | Expired |

## તમારા Case માં શું થયું?

### Timeline:

```
1. 15:00 - તમે AutoPay request મોકલી
   Status: ACTIVATION_IN_PROGRESS

2. 15:01 - તમે ફોન પર decline કર્યું
   Status: હજુ પણ ACTIVATION_IN_PROGRESS (PhonePe processing)

3. 15:02 - તમે API call કરી
   Status: હજુ પણ ACTIVATION_IN_PROGRESS (PhonePe still updating)

4. 15:10 - PhonePe database update થયું
   Status: FAILED

5. 15:11 - તમે ફરીથી API call કરો
   Status: FAILED ✅
```

## કેવી રીતે Verify કરવું?

### Method 1: API Call કરો (5-10 મિનિટ પછી)

```bash
GET {{base_url}}/api/phonepe/autopay/status/MS_699c1e39bec881771839033
```

**Expected Response (after PhonePe updates):**
```json
{
    "data": {
        "state": "FAILED",
        "phonepe_state": "FAILED",
        "is_active": false
    }
}
```

### Method 2: Test Page પર Check કરો

```
1. http://localhost/git_jignasha/craftyart/public/phonepe/simple-payment-test ખોલો

2. તમારું transaction શોધો

3. "🔍 Status" button click કરો

4. 5-10 મિનિટ રાહ જુઓ

5. ફરીથી "🔍 Status" click કરો

6. Status FAILED બતાવશે
```

### Method 3: Database Check કરો

```sql
-- Check current status
SELECT 
    merchant_subscription_id,
    status,
    subscription_status,
    updated_at
FROM phonepe_subscriptions
WHERE merchant_subscription_id = 'MS_699c1e39bec881771839033';

-- Expected after PhonePe updates:
-- status: FAILED
-- subscription_status: FAILED
```

## PhonePe Dashboard પર Check કરો

### PhonePe Merchant Dashboard:

1. Login કરો: https://business.phonepe.com/
2. Subscriptions section માં જાઓ
3. તમારું subscription ID શોધો: `OMS2602231500342493490289W`
4. Status check કરો

**Note:** Dashboard પર પણ status update થવામાં સમય લાગે છે.

## Real-Time Status માટે શું કરવું?

### PhonePe Webhook Setup કરો

PhonePe webhook મોકલે છે જ્યારે status બદલાય છે:

```php
// routes/api.php
Route::post('phonepe/autopay/webhook', [PhonePeAutoPayController::class, 'handleWebhook']);

// PhonePeAutoPayController.php
public function handleWebhook(Request $request)
{
    $data = $request->all();
    
    // PhonePe sends status update
    if (isset($data['state'])) {
        $subscription = PhonePeSubscription::where('phonepe_subscription_id', $data['subscriptionId'])
            ->first();
            
        if ($subscription) {
            $subscription->subscription_status = $data['state'];
            $subscription->status = $this->mapPhonePeStatusToLocal($data['state']);
            $subscription->save();
            
            Log::info('📥 PhonePe Webhook Received', [
                'subscription_id' => $data['subscriptionId'],
                'new_status' => $data['state']
            ]);
        }
    }
    
    return response()->json(['success' => true]);
}
```

### Webhook URL Configure કરો:

1. PhonePe Dashboard માં જાઓ
2. Webhook Settings
3. Add URL: `https://yourdomain.com/api/phonepe/autopay/webhook`
4. Save

હવે જ્યારે user decline કરશે, PhonePe તરત જ webhook મોકલશે!

## Testing માટે Tips

### 1. Sandbox Environment માં

Sandbox માં status updates ધીમા હોય છે:
- Production: 2-5 મિનિટ
- Sandbox: 10-15 મિનિટ

### 2. Production Environment માં

Production માં faster updates:
- Status update: 1-3 મિનિટ
- Webhook: Real-time (10-30 સેકંડ)

### 3. Manual Status Refresh

જો status update નથી થતું:

```php
// Force refresh from PhonePe
$token = $this->tokenService->getAccessToken();
$url = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";

$response = Http::withHeaders([
    "Authorization" => "O-Bearer " . $token
])->get($url);

$data = $response->json();
// Check $data['state']
```

## Summary (સારાંશ)

### તમારી સમસ્યા:
✅ તમે decline કર્યું છે
✅ API હજુ ACTIVATION_IN_PROGRESS બતાવે છે

### કારણ:
⏰ PhonePe status update થવામાં 5-10 મિનિટ લાગે છે

### સમાધાન:
1. 5-10 મિનિટ રાહ જુઓ
2. ફરીથી status check API call કરો
3. Status FAILED બતાવશે

### API Changes:
✅ હવે API mapped status return કરે છે
✅ `state` field માં normalized status
✅ `phonepe_state` field માં original PhonePe status
✅ `is_active` field બતાવે છે કે subscription active છે કે નહીં

### Next Steps:
1. થોડી વાર રાહ જુઓ (5-10 મિનિટ)
2. Status check API call કરો
3. Status FAILED હોવું જોઈએ
4. જો હજુ પણ ACTIVATION_IN_PROGRESS છે, તો PhonePe support ને contact કરો

### Webhook Setup (Recommended):
- Real-time status updates માટે webhook configure કરો
- Status changes તરત જ મળશે
- No need to poll API repeatedly
