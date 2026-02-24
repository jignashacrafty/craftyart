# PhonePe AutoPay Status Flow Explanation

## Status Flow Overview

```
PENDING → ACTIVE → (Redemption) → COMPLETED
   ↓
FAILED/CANCELLED
```

## Status Meanings

### 1. PENDING ⏳
**What it means:**
- Subscription setup request has been sent to PhonePe
- User has received UPI notification on their phone
- User has NOT approved the mandate yet

**What to do:**
- Wait for user to approve on their phone
- Check status periodically using "🔍 Status" button
- User needs to open their UPI app and approve

**How to check:**
```javascript
// Click "🔍 Status" button in the UI
// OR call API:
GET /api/phonepe/autopay/status/{merchantSubscriptionId}
```

### 2. ACTIVE ✅
**What it means:**
- User has approved the mandate on their phone
- Subscription is now active and ready
- You can now trigger auto-debit payments

**What to do:**
- You can now trigger redemption (auto-debit)
- Click "💳 Debit" button to charge the user
- Or wait for scheduled auto-debit

**How it becomes ACTIVE:**
1. User receives UPI notification
2. User opens their UPI app (PhonePe, GPay, etc.)
3. User approves the mandate
4. PhonePe updates status to ACTIVE
5. Your status check API will show ACTIVE

### 3. COMPLETED ✔️
**What it means:**
- Payment has been successfully processed
- Money has been debited from user's account
- Transaction is complete

### 4. FAILED ❌
**What it means:**
- User declined the mandate
- Payment failed
- Technical error occurred

**Common reasons:**
- User clicked "Decline" on UPI app
- Insufficient balance
- UPI ID invalid
- Network timeout

### 5. CANCELLED 🚫
**What it means:**
- Subscription was cancelled by merchant or user
- No more auto-debits will occur

## How Status Checking Works

### In the Test Page (`/phonepe/simple-payment-test`)

1. **After sending payment request:**
   ```
   Status: PENDING
   AutoPay: ⏸️ Inactive
   ```

2. **User approves on phone:**
   ```
   Status: ACTIVE
   AutoPay: ✅ Active
   ```

3. **Click "🔍 Status" button:**
   - Calls PhonePe API to get latest status
   - Updates database with new status
   - Refreshes the table to show updated status

### Status Check API Flow

```php
// Controller: PhonePeSimplePaymentTestController.php
public function checkSubscriptionStatus(Request $request)
{
    // 1. Get OAuth token
    $token = $this->getAccessToken();
    
    // 2. Call PhonePe status API
    $url = "https://api.phonepe.com/apis/pg/subscriptions/v2/{$merchantSubscriptionId}/status?details=true";
    $response = Http::withHeaders([
        "Authorization" => "O-Bearer " . $token
    ])->get($url);
    
    $data = $response->json();
    
    // 3. Update local database
    $transaction->status = $data['state']; // PENDING, ACTIVE, FAILED, etc.
    $transaction->is_autopay_active = in_array($data['state'], ['ACTIVE', 'COMPLETED']);
    $transaction->save();
    
    // 4. Return status to frontend
    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}
```

### Frontend Status Display

```javascript
// In resources/views/phonepe_simple_payment_test.blade.php

function checkStatus(subscriptionId, orderId) {
    $.ajax({
        url: '/phonepe/check_subscription_status',
        data: { merchantSubscriptionId: subscriptionId },
        success: function(response) {
            // Display current state
            let state = response.data.state; // PENDING, ACTIVE, FAILED, etc.
            
            // Show message to user
            $('#resultBox').html(`
                <h5>📊 Subscription Status</h5>
                <p><strong>Current State:</strong> ${state}</p>
            `);
            
            // Reload table to show updated status
            loadHistory();
        }
    });
}
```

## Why Status Shows PENDING

### Reason 1: User Hasn't Approved Yet
**Most common reason:**
- User received notification but hasn't opened UPI app
- User is busy and will approve later
- User didn't see the notification

**Solution:**
- Ask user to check their phone
- User should open UPI app
- Look for pending mandate approval
- Approve the mandate

### Reason 2: Notification Delay
**Sometimes:**
- UPI notification takes 1-2 minutes to arrive
- Network delay
- PhonePe server processing time

**Solution:**
- Wait 2-3 minutes
- Click "🔍 Status" button to refresh
- Check user's phone for notification

### Reason 3: Wrong UPI ID
**If UPI ID is invalid:**
- Notification won't be sent
- Status will remain PENDING forever
- No error message from PhonePe

**Solution:**
- Verify UPI ID is correct
- Format: `username@bankname` (e.g., `vrajsurani606@okaxis`)
- Try with different UPI ID

## Testing Flow

### Step 1: Send Payment Request
```
1. Fill form with UPI ID, amount, mobile
2. Click "📲 Send AutoPay Request to My UPI"
3. Wait for success message
4. Note the Subscription ID
```

### Step 2: Check Your Phone
```
1. Open your UPI app (PhonePe, GPay, Paytm, etc.)
2. Look for "Mandate Approval" or "AutoPay Request"
3. Review the details
4. Click "Approve" or "Accept"
```

### Step 3: Verify Status Changed
```
1. Go back to test page
2. Click "🔍 Status" button for that transaction
3. Status should now show "ACTIVE"
4. AutoPay badge should show "✅ Active"
```

### Step 4: Test Auto-Debit
```
1. Click "💳 Debit" button
2. Money will be debited from your account
3. Status will change to "COMPLETED"
4. AutoPay count will increase
```

## Common Issues

### Issue 1: Status Stuck on PENDING
**Symptoms:**
- Status never changes from PENDING
- User says they approved on phone
- "🔍 Status" button still shows PENDING

**Debugging:**
1. Check PhonePe response in logs:
   ```bash
   tail -f storage/logs/laravel.log | grep "subscription status"
   ```

2. Verify subscription ID is correct:
   ```sql
   SELECT * FROM phonepe_transactions 
   WHERE merchant_subscription_id = 'MS_...';
   ```

3. Check PhonePe API response:
   - Look for `state` field in response
   - Check if there's an error message
   - Verify OAuth token is valid

**Solutions:**
- User may have declined - ask them to check
- Try creating new subscription with same UPI
- Check if UPI ID is correct
- Verify PhonePe credentials are production (not sandbox)

### Issue 2: Status Shows FAILED
**Symptoms:**
- Status changes to FAILED
- User can't approve mandate

**Common Reasons:**
- User clicked "Decline" on phone
- UPI ID doesn't exist
- Bank server down
- Insufficient balance (for first transaction)

**Solutions:**
- Create new subscription request
- Verify UPI ID is correct
- Ask user to try different UPI app
- Check user's bank account status

### Issue 3: Can't Trigger Auto-Debit
**Symptoms:**
- Status is ACTIVE
- But "💳 Debit" button fails

**Reasons:**
- Using sandbox credentials (redemption not supported in sandbox)
- Already debited today (can't debit twice same day)
- User cancelled mandate from their app

**Solutions:**
- Use production credentials for redemption
- Wait 24 hours before next debit
- Check status again to verify still ACTIVE

## API Routes Summary

### 1. Setup Subscription
```
POST /api/phonepe/autopay/setup
Body: { user_id, plan_id, amount, upi }
Response: { merchant_subscription_id, state: "PENDING" }
```

### 2. Check Status
```
GET /api/phonepe/autopay/status/{merchantSubscriptionId}
Response: { state: "PENDING|ACTIVE|FAILED|COMPLETED" }
```

### 3. Trigger Redemption
```
POST /api/phonepe/autopay/redeem
Body: { merchant_subscription_id }
Response: { success, merchant_order_id }
```

### 4. Cancel Subscription
```
POST /api/phonepe/autopay/cancel
Body: { merchant_subscription_id }
Response: { success, message }
```

## Database Tables

### phonepe_transactions
```sql
- merchant_subscription_id (unique identifier)
- status (PENDING, ACTIVE, FAILED, COMPLETED)
- is_autopay_active (boolean)
- autopay_count (number of successful debits)
- last_autopay_at (timestamp)
- next_autopay_at (timestamp)
```

### phonepe_notifications
```sql
- notification_type (SUBSCRIPTION_SETUP, STATUS_CHECK, PAYMENT_SUCCESS, etc.)
- status (PENDING, SUCCESS, FAILED)
- is_processed (boolean)
- processed_at (timestamp)
```

## Logs to Check

### Laravel Logs
```bash
# Check subscription status calls
tail -f storage/logs/laravel.log | grep "Checking subscription status"

# Check PhonePe API responses
tail -f storage/logs/laravel.log | grep "PhonePe"

# Check OAuth token generation
tail -f storage/logs/laravel.log | grep "OAuth"
```

### Database Queries
```sql
-- Check transaction status
SELECT merchant_subscription_id, status, is_autopay_active, created_at 
FROM phonepe_transactions 
ORDER BY created_at DESC 
LIMIT 10;

-- Check notifications
SELECT notification_type, status, created_at 
FROM phonepe_notifications 
ORDER BY created_at DESC 
LIMIT 10;
```

## Summary

**PENDING status is NORMAL** when:
- ✅ User just received notification
- ✅ User hasn't opened UPI app yet
- ✅ User is reviewing the mandate

**PENDING becomes ACTIVE** when:
- ✅ User opens UPI app
- ✅ User clicks "Approve" on mandate
- ✅ PhonePe processes the approval

**To fix PENDING status:**
1. Ask user to check their phone
2. User should open UPI app
3. User should approve the mandate
4. Click "🔍 Status" button to refresh
5. Status will change to ACTIVE

**The status check API is working correctly** - it's just waiting for user action!
