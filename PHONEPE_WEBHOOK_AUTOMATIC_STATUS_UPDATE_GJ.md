# PhonePe Webhook - Automatic Status Update (Gujarati)

## સમસ્યા જે હતી

### તમારો પ્રશ્ન:
"Payment status automatically failed કે success થાય છે કે નહીં? હાલમાં બધા status PENDING જ બતાવે છે."

### મૂળ સમસ્યા:
1. ❌ Webhook handler નહોતું
2. ❌ Status manually check કરવું પડતું હતું
3. ❌ User approve/decline કરે પછી પણ status PENDING જ રહેતું
4. ❌ Real-time updates નહોતા મળતા

## હવે શું Fixed છે? ✅

### 1. Webhook Handler Added
```php
// app/Http/Controllers/Api/PhonePeAutoPayController.php
public function handleWebhook(Request $request)
{
    // PhonePe automatically sends status updates
    // When user approves → Status: ACTIVE
    // When user declines → Status: FAILED
    // When payment succeeds → Status: COMPLETED
}
```

### 2. Automatic Status Updates
હવે PhonePe automatically status update કરશે:

| Event | PhonePe Sends | Status Updates To |
|-------|---------------|-------------------|
| User approves mandate | ACTIVE | ACTIVE ✅ |
| User declines mandate | FAILED | FAILED ❌ |
| Payment succeeds | COMPLETED | COMPLETED ✔️ |
| Payment fails | PAYMENT_FAILED | FAILED ❌ |
| Subscription cancelled | CANCELLED | CANCELLED 🚫 |

### 3. Order Status પણ Update થાય છે
```php
// Subscription status → Order status
ACTIVE/COMPLETED → order.status = 'completed'
FAILED → order.status = 'failed'
```

## કેવી રીતે કામ કરે છે?

### Flow Diagram:

```
1. User Payment Request મોકલે
   ↓
2. PhonePe Subscription Create થાય
   Status: PENDING
   ↓
3. User ફોન પર Approve/Decline કરે
   ↓
4. PhonePe Webhook મોકલે છે (Automatic!)
   POST /api/phonepe/autopay/webhook
   ↓
5. આપણું System Status Update કરે છે
   Database: subscription.status = 'ACTIVE' or 'FAILED'
   ↓
6. Order Status પણ Update થાય છે
   order.status = 'completed' or 'failed'
   ↓
7. ✅ Done! Real-time update!
```

### Timeline Example:

```
15:00:00 - User payment request મોકલે
           Status: PENDING

15:00:05 - User ફોન પર notification મળે
           Status: PENDING (still)

15:00:30 - User "Approve" click કરે
           Status: PENDING (still processing)

15:00:35 - PhonePe webhook મોકલે છે
           POST /api/phonepe/autopay/webhook
           Body: { "state": "ACTIVE", ... }

15:00:36 - આપણું system status update કરે
           Status: ACTIVE ✅

15:00:37 - Order status પણ update થાય
           order.status = 'completed' ✅
```

## Webhook URL Configuration

### PhonePe Dashboard માં Setup:

1. **Login કરો:**
   ```
   https://business.phonepe.com/
   ```

2. **Settings → Webhooks જાઓ**

3. **Webhook URL Add કરો:**
   ```
   Production: https://yourdomain.com/api/phonepe/autopay/webhook
   Sandbox: https://yourdomain.com/api/phonepe/autopay/webhook
   ```

4. **Events Select કરો:**
   - ✅ Subscription Status Change
   - ✅ Payment Success
   - ✅ Payment Failed
   - ✅ Subscription Cancelled

5. **Save કરો**

### Webhook URL Format:
```
{{base_url}}/api/phonepe/autopay/webhook

Examples:
- Local: http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/webhook
- Production: https://craftyartapp.com/api/phonepe/autopay/webhook
```

## Webhook Payload Example

### When User Approves:
```json
{
    "merchantSubscriptionId": "MS_699c1e39bec881771839033",
    "subscriptionId": "OMS2602231500342493490289W",
    "state": "ACTIVE",
    "transactionId": "TXN123456789",
    "amount": 100,
    "currency": "INR",
    "timestamp": "2026-02-23T15:00:35Z"
}
```

### When User Declines:
```json
{
    "merchantSubscriptionId": "MS_699c1e39bec881771839033",
    "subscriptionId": "OMS2602231500342493490289W",
    "state": "FAILED",
    "transactionId": "TXN123456789",
    "failureReason": "User declined mandate",
    "timestamp": "2026-02-23T15:00:35Z"
}
```

## Testing Webhook

### Method 1: PhonePe Sandbox
```bash
# 1. Create subscription
POST {{base_url}}/api/phonepe/autopay/setup

# 2. User approves on phone
# (PhonePe automatically sends webhook)

# 3. Check status
GET {{base_url}}/api/phonepe/autopay/status/MS_xxx

# Response:
{
    "data": {
        "state": "ACTIVE",  // ✅ Automatically updated!
        "is_active": true
    }
}
```

### Method 2: Manual Webhook Test
```bash
# Simulate PhonePe webhook
POST {{base_url}}/api/phonepe/autopay/webhook
Content-Type: application/json

{
    "merchantSubscriptionId": "MS_699c1e39bec881771839033",
    "subscriptionId": "OMS2602231500342493490289W",
    "state": "ACTIVE",
    "transactionId": "TEST123"
}

# Response:
{
    "success": true,
    "message": "Webhook processed successfully"
}
```

### Method 3: Check Logs
```bash
# Laravel logs માં webhook check કરો
tail -f storage/logs/laravel.log | grep "PhonePe Webhook"

# Expected output:
📥 PhonePe Webhook Received
✅ Webhook processed successfully
✅ Order status updated
```

## Database Changes

### Subscription Status Update:
```sql
-- Before webhook
SELECT status, subscription_status FROM phonepe_subscriptions WHERE id = 256;
-- status: PENDING
-- subscription_status: ACTIVATION_IN_PROGRESS

-- After webhook (user approved)
SELECT status, subscription_status FROM phonepe_subscriptions WHERE id = 256;
-- status: ACTIVE
-- subscription_status: ACTIVE
```

### Order Status Update:
```sql
-- Before webhook
SELECT status FROM orders WHERE id = 123;
-- status: pending

-- After webhook (user approved)
SELECT status FROM orders WHERE id = 123;
-- status: completed
```

### Webhook History:
```sql
-- Check webhook history in metadata
SELECT metadata FROM phonepe_subscriptions WHERE id = 256;

-- Output:
{
    "webhooks": [
        {
            "received_at": "2026-02-23T15:00:35Z",
            "state": "ACTIVE",
            "transaction_id": "TXN123",
            "payload": {...}
        }
    ]
}
```

## Manual Sync (Backup Method)

જો webhook miss થાય તો manual sync કરી શકો:

### API Call:
```php
// Internal use only
$controller = new PhonePeAutoPayController($tokenService);
$result = $controller->syncSubscriptionStatus('MS_699c1e39bec881771839033');

// Result:
[
    'success' => true,
    'old_status' => 'PENDING',
    'new_status' => 'ACTIVE',
    'phonepe_state' => 'ACTIVE'
]
```

### Artisan Command (Optional):
```bash
# Create command for manual sync
php artisan phonepe:sync-status MS_699c1e39bec881771839033
```

## Status Flow Complete

### Before (Manual):
```
1. User payment request → PENDING
2. User approves → Still PENDING
3. Manual API call → Check status
4. API returns → ACTIVE
5. Manual update → Update database
```

### After (Automatic):
```
1. User payment request → PENDING
2. User approves → PhonePe sends webhook
3. Webhook received → Auto update to ACTIVE ✅
4. Order updated → Auto update to completed ✅
5. Done! → No manual intervention needed
```

## All Possible Status Updates

| User Action | PhonePe Webhook | Subscription Status | Order Status |
|-------------|-----------------|---------------------|--------------|
| Approves mandate | state: ACTIVE | ACTIVE ✅ | completed |
| Declines mandate | state: FAILED | FAILED ❌ | failed |
| Payment succeeds | state: COMPLETED | COMPLETED ✔️ | completed |
| Payment fails | state: PAYMENT_FAILED | FAILED ❌ | failed |
| Cancels subscription | state: CANCELLED | CANCELLED 🚫 | cancelled |
| Mandate expires | state: EXPIRED | EXPIRED ⏰ | expired |

## Error Handling

### Webhook Fails:
```php
// System still returns 200 to PhonePe
// Logs error for debugging
// PhonePe will retry webhook after some time
```

### Subscription Not Found:
```php
// Returns 404
// Logs warning
// PhonePe stops retrying
```

### Database Error:
```php
// Returns 200 (to avoid PhonePe retries)
// Logs error
// Manual sync needed
```

## Monitoring & Debugging

### Check Webhook Logs:
```bash
# Real-time monitoring
tail -f storage/logs/laravel.log | grep "Webhook"

# Search specific subscription
grep "MS_699c1e39bec881771839033" storage/logs/laravel.log
```

### Check Webhook History:
```sql
-- Get all webhooks for a subscription
SELECT 
    id,
    merchant_subscription_id,
    status,
    JSON_EXTRACT(metadata, '$.webhooks') as webhook_history
FROM phonepe_subscriptions
WHERE merchant_subscription_id = 'MS_699c1e39bec881771839033';
```

### Webhook Statistics:
```sql
-- Count webhooks received today
SELECT 
    DATE(created_at) as date,
    COUNT(*) as webhook_count
FROM phonepe_subscriptions
WHERE JSON_LENGTH(metadata, '$.webhooks') > 0
GROUP BY DATE(created_at);
```

## Production Checklist

### Before Going Live:

- [ ] Webhook URL configured in PhonePe Dashboard
- [ ] Webhook route accessible (no authentication required)
- [ ] Logs enabled for debugging
- [ ] Database has all status ENUM values
- [ ] Test webhook with sandbox
- [ ] Monitor logs for first few webhooks
- [ ] Set up alerts for webhook failures

### After Going Live:

- [ ] Monitor webhook success rate
- [ ] Check status update accuracy
- [ ] Verify order status updates
- [ ] Set up automated sync for missed webhooks
- [ ] Create dashboard for webhook monitoring

## Summary (સારાંશ)

### હવે શું થશે:

✅ **Automatic Status Updates:**
- User approve કરે → Status automatically ACTIVE થાય છે
- User decline કરે → Status automatically FAILED થાય છે
- Payment success → Status automatically COMPLETED થાય છે

✅ **Real-time Updates:**
- Webhook થી instant updates
- Manual API call ની જરૂર નથી
- Order status પણ auto-update થાય છે

✅ **Proper Tracking:**
- Webhook history stored in metadata
- Logs માં complete tracking
- Easy debugging

### Next Steps:

1. **PhonePe Dashboard માં webhook URL configure કરો**
2. **Test payment કરો અને webhook verify કરો**
3. **Logs check કરો કે webhook receive થયું કે નહીં**
4. **Production માં deploy કરો**

હવે તમારી payment status automatically update થશે! 🎉
