# PhonePe Status ENUM Fix

## Issue

**Error:**
```
SQLSTATE[01000]: Warning: 1265 Data truncated for column 'status' at row 1
```

**Root Cause:**
The `status` column in `phonepe_subscriptions` table was defined as an ENUM with limited values that didn't include 'FAILED'.

## Previous ENUM Values

```sql
ENUM('PENDING', 'ACTIVE', 'PAUSED', 'CANCELLED', 'EXPIRED', 'PAYMENT_FAILED')
```

**Missing Values:**
- `FAILED` ❌
- `COMPLETED` ❌
- `UNKNOWN` ❌

## Fix Applied

### Migration Created:
`database/migrations/2026_02_23_000000_add_all_phonepe_statuses_to_subscriptions.php`

### New ENUM Values:
```sql
ENUM('PENDING', 'ACTIVE', 'COMPLETED', 'FAILED', 'CANCELLED', 'EXPIRED', 'PAUSED', 'PAYMENT_FAILED', 'UNKNOWN')
```

### All Status Values Now Supported:

| Status | Description | Use Case |
|--------|-------------|----------|
| PENDING | Initial state | User hasn't approved yet |
| ACTIVE | Subscription active | User approved mandate |
| COMPLETED | Payment completed | Transaction successful |
| FAILED | Setup/Payment failed | User declined or error |
| CANCELLED | Subscription cancelled | Merchant or user cancelled |
| EXPIRED | Subscription expired | Mandate validity ended |
| PAUSED | Subscription paused | Temporarily stopped |
| PAYMENT_FAILED | Payment failed | Auto-debit failed |
| UNKNOWN | Unknown state | Fallback for unexpected values |

## Migration Executed

```bash
php artisan migrate --path=database/migrations/2026_02_23_000000_add_all_phonepe_statuses_to_subscriptions.php
```

**Result:** ✅ Migrated successfully (206.98ms)

## Testing

### Test 1: Update to FAILED
```sql
UPDATE phonepe_subscriptions 
SET status = 'FAILED' 
WHERE id = 256;
```
**Result:** ✅ Success (no more truncation error)

### Test 2: API Status Check
```bash
GET {{base_url}}/api/phonepe/autopay/status/MS_699c1e39bec881771839033
```

**Expected Response:**
```json
{
    "statusCode": 200,
    "success": true,
    "msg": "Subscription status retrieved",
    "data": {
        "state": "FAILED",
        "phonepe_state": "FAILED",
        "is_active": false
    }
}
```

### Test 3: All Status Values
```php
// Test all status values can be saved
$statuses = ['PENDING', 'ACTIVE', 'COMPLETED', 'FAILED', 'CANCELLED', 'EXPIRED', 'PAUSED', 'PAYMENT_FAILED', 'UNKNOWN'];

foreach ($statuses as $status) {
    $subscription->status = $status;
    $subscription->save();
    echo "✅ {$status} saved successfully\n";
}
```

## Status Mapping Reference

### PhonePe API → Database

| PhonePe Returns | Saved as | Display as |
|-----------------|----------|------------|
| PENDING | PENDING | PENDING ⏳ |
| ACTIVATION_IN_PROGRESS | PENDING | PENDING ⏳ |
| ACTIVE | ACTIVE | ACTIVE ✅ |
| COMPLETED | COMPLETED | COMPLETED ✔️ |
| FAILED | FAILED | FAILED ❌ |
| CANCELLED | CANCELLED | CANCELLED 🚫 |
| EXPIRED | EXPIRED | EXPIRED ⏰ |

## Files Modified

1. **database/migrations/2026_02_23_000000_add_all_phonepe_statuses_to_subscriptions.php** (NEW)
   - Added migration to expand ENUM values
   - Includes all possible PhonePe status values

## Database Schema

### Before:
```sql
CREATE TABLE `phonepe_subscriptions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `status` enum('PENDING','ACTIVE','PAUSED','CANCELLED','EXPIRED','PAYMENT_FAILED') DEFAULT 'PENDING',
    ...
);
```

### After:
```sql
CREATE TABLE `phonepe_subscriptions` (
    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
    `status` enum('PENDING','ACTIVE','COMPLETED','FAILED','CANCELLED','EXPIRED','PAUSED','PAYMENT_FAILED','UNKNOWN') DEFAULT 'PENDING',
    ...
);
```

## Deployment Notes

### For Production:

1. **Backup database first:**
```bash
mysqldump -u username -p database_name phonepe_subscriptions > backup_phonepe_subscriptions.sql
```

2. **Run migration:**
```bash
php artisan migrate --path=database/migrations/2026_02_23_000000_add_all_phonepe_statuses_to_subscriptions.php
```

3. **Verify:**
```sql
SHOW COLUMNS FROM phonepe_subscriptions WHERE Field = 'status';
```

Expected output:
```
Field: status
Type: enum('PENDING','ACTIVE','COMPLETED','FAILED','CANCELLED','EXPIRED','PAUSED','PAYMENT_FAILED','UNKNOWN')
Default: PENDING
```

### Rollback (if needed):

```bash
php artisan migrate:rollback --path=database/migrations/2026_02_23_000000_add_all_phonepe_statuses_to_subscriptions.php
```

## Summary

✅ Fixed ENUM truncation error
✅ Added all missing status values (FAILED, COMPLETED, UNKNOWN)
✅ Migration executed successfully
✅ API can now save all PhonePe status values
✅ No more "Data truncated" errors

The `phonepe_subscriptions` table now supports all possible status values from PhonePe API!
