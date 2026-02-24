# Purchase History ane User Subscriptions Seeder Guide

## Overview
Aa seeder `purchase_history` ane `manage_subscriptions` (user_subscriptions) tables ma proper relationship sathe fake data add kare che.

## Features

### ✅ Proper Relationships
- Existing `user_data` table ma thi users lai ne data generate kare che
- `subscriptions` table ma thi plans lai ne purchase history create kare che
- Har successful payment mate `manage_subscriptions` ma entry create thay che
- PhonePe AutoPay fields pan properly set thay che

### ✅ Realistic Data
- Last 30 days ma random purchase dates
- Multiple payment methods: PhonePe, Razorpay, UPI, Card, NetBanking
- 80% success rate, 20% failed/pending payments
- PhonePe transactions mate AutoPay details
- Proper transaction IDs ane payment IDs

### ✅ Data Validation
- User existence check kare che
- Plan existence check kare che
- Only successful payments mate subscriptions create thay che

## Prerequisites (Pehla aa karo)

### 1. User Data Table ma Users hova joiye
```sql
-- Check karo ke users che ke nai
SELECT COUNT(*) FROM user_data;
```

Jyare users na hoy tyare pehla users add karo.

### 2. Subscription Plans hova joiye
```bash
# Subscription plans seeder run karo
php artisan db:seed --class=SubscriptionPlansSeeder
```

## Installation Steps

### Step 1: Database Connection Check
```bash
# .env file ma check karo ke database properly configured che
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crafty_revenue
```

### Step 2: Seeder Run Karo
```bash
# Seeder run karo
php artisan db:seed --class=PurchaseHistoryAndSubscriptionsSeeder
```

### Step 3: Verify Data
```sql
-- Purchase history check karo
SELECT COUNT(*) FROM purchase_history;

-- User subscriptions check karo
SELECT COUNT(*) FROM manage_subscriptions;

-- Relationship check karo
SELECT 
    ph.id,
    ph.user_id,
    ph.product_id,
    ph.amount,
    ph.payment_method,
    ph.payment_status,
    ms.package_name,
    ms.validity
FROM purchase_history ph
LEFT JOIN manage_subscriptions ms ON ph.user_id = ms.user_id
ORDER BY ph.created_at DESC
LIMIT 10;
```

## Data Structure

### Purchase History Fields
```php
- user_id                    // user_data.uid thi
- product_id                 // subscriptions.id thi
- product_type               // 1 = subscription
- transaction_id             // Unique transaction ID
- payment_id                 // Unique payment ID
- currency_code              // INR
- amount                     // Plan price
- payment_method             // PhonePe, Razorpay, etc.
- from_where                 // Web / Mobile
- contact_no                 // User contact number
- payment_status             // 1 = Success, 0 = Failed
- status                     // 1 = Active, 0 = Inactive

// PhonePe AutoPay Fields (optional)
- phonepe_merchant_order_id
- phonepe_subscription_id
- phonepe_order_id
- phonepe_transaction_id
- is_autopay_enabled
- autopay_status
- autopay_activated_at
- next_autopay_date
- autopay_count
```

### User Subscriptions (manage_subscriptions) Fields
```php
- user_id                    // user_data.uid thi
- package_name               // Plan name
- desc                       // Plan description
- validity                   // Days
- actual_price               // Original price
- price                      // Discounted price
- months                     // Plan duration in months
- has_offer                  // 1 = Has offer
- status                     // 1 = Active
```

## Generated Data Examples

### Example 1: Successful PhonePe Payment with AutoPay
```php
Purchase History:
- user_id: "USER123"
- product_id: 2 (Monthly Plan)
- amount: 1499.00
- payment_method: "PhonePe"
- payment_status: 1 (Success)
- phonepe_subscription_id: "SUB_1234567890_1"
- is_autopay_enabled: 1
- autopay_status: "ACTIVE"

User Subscription:
- user_id: "USER123"
- package_name: "Crafty Art Pro - Monthly"
- validity: 30
- price: 1499.00
- status: 1 (Active)
```

### Example 2: Failed Razorpay Payment
```php
Purchase History:
- user_id: "USER456"
- product_id: 3 (6 Months Plan)
- amount: 6999.00
- payment_method: "Razorpay"
- payment_status: 0 (Failed)

User Subscription:
- (No entry created - payment failed)
```

## Troubleshooting

### Issue 1: "user_data table ma koi users nathi"
**Solution:**
```sql
-- Sample users add karo user_data table ma
INSERT INTO user_data (uid, name, email, created_at, updated_at) VALUES
('USER001', 'Test User 1', 'test1@example.com', NOW(), NOW()),
('USER002', 'Test User 2', 'test2@example.com', NOW(), NOW()),
('USER003', 'Test User 3', 'test3@example.com', NOW(), NOW());
```

### Issue 2: "subscriptions table ma koi plans nathi"
**Solution:**
```bash
php artisan db:seed --class=SubscriptionPlansSeeder
```

### Issue 3: Database connection error
**Solution:**
```bash
# Database credentials check karo .env file ma
# Database service running che ke nai check karo
mysql -u root -p
```

## Data Cleanup (Jyare data delete karvo hoy)

```sql
-- Purchase history clear karo
TRUNCATE TABLE purchase_history;

-- User subscriptions clear karo
TRUNCATE TABLE manage_subscriptions;

-- Pheri thi seeder run karo
```

## Testing Queries

### 1. User wise purchase history
```sql
SELECT 
    user_id,
    COUNT(*) as total_purchases,
    SUM(CASE WHEN payment_status = 1 THEN 1 ELSE 0 END) as successful,
    SUM(CASE WHEN payment_status = 0 THEN 1 ELSE 0 END) as failed,
    SUM(amount) as total_amount
FROM purchase_history
GROUP BY user_id;
```

### 2. Payment method wise breakdown
```sql
SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(amount) as total_amount,
    AVG(amount) as avg_amount
FROM purchase_history
WHERE payment_status = 1
GROUP BY payment_method;
```

### 3. Active subscriptions
```sql
SELECT 
    ms.*,
    ph.payment_method,
    ph.created_at as purchase_date
FROM manage_subscriptions ms
JOIN purchase_history ph ON ms.user_id = ph.user_id
WHERE ms.status = 1
ORDER BY ph.created_at DESC;
```

### 4. PhonePe AutoPay enabled users
```sql
SELECT 
    user_id,
    phonepe_subscription_id,
    autopay_status,
    next_autopay_date,
    autopay_count
FROM purchase_history
WHERE is_autopay_enabled = 1
AND autopay_status = 'ACTIVE';
```

## Summary

✅ 10 users mate data generate thase
✅ Existing user_data table thi users lai ne
✅ Existing subscriptions table thi plans lai ne
✅ Proper relationships maintain thase
✅ Realistic payment data with success/failure
✅ PhonePe AutoPay fields properly set thase
✅ Only successful payments mate subscriptions create thase

## Next Steps

1. Seeder run karo
2. Data verify karo testing queries thi
3. Application ma test karo ke data properly display thay che ke nai
4. Jyare production ma deploy karo tyare aa seeder run na karjo!

---

**Note:** Aa seeder only development/testing mate che. Production database ma aa seeder run na karjo!
