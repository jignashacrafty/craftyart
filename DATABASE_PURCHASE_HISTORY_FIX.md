# Purchase History Database Issue - Fix Documentation

## Problem Identified

The `purchase_history` table exists in **TWO** databases:

1. ✅ **crafty_db (mysql connection)** - 34 records (CORRECT LOCATION)
2. ⚠️ **crafty_revenue (crafty_revenue_mysql connection)** - 13 records (DUPLICATE - WRONG LOCATION)

## Root Cause

The `purchase_history` table was incorrectly created or migrated to the `crafty_revenue` database. This table should ONLY exist in the main `crafty_db` database.

## Current Database Structure

### crafty_db (mysql) - Main Database
Should contain:
- ✅ `purchase_history` - Template/Video/Caricature purchases
- ✅ `orders` - All order records
- ✅ `users` - User accounts
- ✅ All other main application tables

### crafty_revenue_mysql - Revenue Database
Should contain:
- ✅ `sales` - Sales revenue tracking
- ✅ `business_support_purchase_history` - Business support purchases
- ❌ `purchase_history` - **SHOULD NOT BE HERE**

## Impact

The duplicate `purchase_history` table in `crafty_revenue` database can cause:
1. Data inconsistency between the two tables
2. Confusion about which table is the source of truth
3. Potential errors when querying purchase history
4. Image loading issues if the wrong database is queried

## Solution

### Step 1: Backup Data (IMPORTANT!)

Before making any changes, backup both tables:

```sql
-- Backup from crafty_db
mysqldump -u root crafty_db purchase_history > backup_purchase_history_crafty_db.sql

-- Backup from crafty_revenue
mysqldump -u root crafty_revenue purchase_history > backup_purchase_history_crafty_revenue.sql
```

### Step 2: Compare Data

Check if there are any records in `crafty_revenue.purchase_history` that don't exist in `crafty_db.purchase_history`:

```sql
-- Check records in crafty_revenue that might not be in crafty_db
SELECT * FROM crafty_revenue.purchase_history 
WHERE id NOT IN (SELECT id FROM crafty_db.purchase_history);
```

### Step 3: Migrate Missing Data (if any)

If there are unique records in `crafty_revenue.purchase_history`, migrate them to `crafty_db`:

```sql
-- Insert missing records from crafty_revenue to crafty_db
INSERT INTO crafty_db.purchase_history 
SELECT * FROM crafty_revenue.purchase_history 
WHERE id NOT IN (SELECT id FROM crafty_db.purchase_history);
```

### Step 4: Drop Duplicate Table

Once data is safely migrated and backed up, drop the duplicate table:

```sql
-- Drop the duplicate table from crafty_revenue
DROP TABLE crafty_revenue.purchase_history;
```

### Step 5: Verify

Run the check script again to verify:

```bash
php check_purchase_history.php
```

Expected output:
```
✅ crafty_db (mysql): XX records found
✅ crafty_revenue_mysql: Table does not exist (correct)
✅ PurchaseHistory Model: XX records
   Connection: mysql
✅ business_support_purchase_history in crafty_revenue_mysql: 5 records
```

## Model Configuration

The `PurchaseHistory` model is correctly configured:

```php
// app/Models/PurchaseHistory.php
class PurchaseHistory extends Model
{
    protected $table = 'purchase_history';
    protected $connection = 'mysql'; // ✅ Correct - uses crafty_db
    
    // ... rest of the model
}
```

## Database Connections Summary

### Correct Structure:

```
crafty_db (mysql)
├── purchase_history ✅
├── orders ✅
├── users ✅
└── [other main tables]

crafty_revenue (crafty_revenue_mysql)
├── sales ✅
└── business_support_purchase_history ✅

crafty_pricing (crafty_pricing_mysql)
├── plans ✅
├── sub_plans ✅
├── payment_configurations ✅
└── [other pricing tables]
```

## Prevention

To prevent this issue in the future:

1. Always specify the database connection in migrations:
   ```php
   Schema::connection('crafty_revenue_mysql')->create('table_name', ...);
   ```

2. Document which tables belong to which database

3. Use proper naming conventions:
   - `crafty_db` - Main application data
   - `crafty_revenue_mysql` - Revenue/sales tracking only
   - `crafty_pricing_mysql` - Pricing/plans only

## Testing After Fix

1. Test purchase history display in admin panel
2. Test order creation and purchase history recording
3. Verify images load correctly
4. Check that no errors appear in logs

## SQL Commands for Quick Fix

```sql
-- 1. Backup (run from command line)
mysqldump -u root crafty_db purchase_history > backup_purchase_history_crafty_db.sql
mysqldump -u root crafty_revenue purchase_history > backup_purchase_history_crafty_revenue.sql

-- 2. Check for missing data
USE crafty_revenue;
SELECT COUNT(*) as revenue_count FROM purchase_history;

USE crafty_db;
SELECT COUNT(*) as main_count FROM purchase_history;

-- 3. If revenue_count > main_count, migrate missing records
INSERT INTO crafty_db.purchase_history 
SELECT * FROM crafty_revenue.purchase_history 
WHERE id NOT IN (SELECT id FROM crafty_db.purchase_history);

-- 4. Drop duplicate table
DROP TABLE crafty_revenue.purchase_history;

-- 5. Verify
SHOW TABLES FROM crafty_revenue LIKE 'purchase_history';
-- Should return empty result
```

## Notes

- The `business_support_purchase_history` table is DIFFERENT from `purchase_history`
- `business_support_purchase_history` correctly belongs in `crafty_revenue_mysql`
- `purchase_history` should ONLY be in `crafty_db`
