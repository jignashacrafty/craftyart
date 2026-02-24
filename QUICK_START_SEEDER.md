# Quick Start - Purchase History & Subscriptions Seeder

## 🚀 3-Step Setup

### Step 1: Check Prerequisites
```bash
# Check if users exist
mysql -u root -p crafty_revenue -e "SELECT COUNT(*) as user_count FROM user_data;"
```
**Expected:** At least 10 users

If no users, add some:
```sql
INSERT INTO user_data (uid, name, email, created_at, updated_at) VALUES
('USER001', 'Test User 1', 'test1@example.com', NOW(), NOW()),
('USER002', 'Test User 2', 'test2@example.com', NOW(), NOW()),
('USER003', 'Test User 3', 'test3@example.com', NOW(), NOW()),
('USER004', 'Test User 4', 'test4@example.com', NOW(), NOW()),
('USER005', 'Test User 5', 'test5@example.com', NOW(), NOW()),
('USER006', 'Test User 6', 'test6@example.com', NOW(), NOW()),
('USER007', 'Test User 7', 'test7@example.com', NOW(), NOW()),
('USER008', 'Test User 8', 'test8@example.com', NOW(), NOW()),
('USER009', 'Test User 9', 'test9@example.com', NOW(), NOW()),
('USER010', 'Test User 10', 'test10@example.com', NOW(), NOW());
```

### Step 2: Seed Subscription Plans (if not done)
```bash
php artisan db:seed --class=SubscriptionPlansSeeder
```

### Step 3: Run Main Seeder
```bash
php artisan db:seed --class=PurchaseHistoryAndSubscriptionsSeeder
```

## ✅ Verify Success

```bash
# Quick check
mysql -u root -p crafty_revenue -e "
SELECT 
    'Purchase History' as table_name, COUNT(*) as count FROM purchase_history
UNION ALL
SELECT 
    'User Subscriptions' as table_name, COUNT(*) as count FROM manage_subscriptions;
"
```

**Expected Output:**
```
+--------------------+-------+
| table_name         | count |
+--------------------+-------+
| Purchase History   |    10 |
| User Subscriptions |     8 |
+--------------------+-------+
```

## 🎯 What You Get

- ✅ 10 purchase history entries
- ✅ ~8 active subscriptions (80% success rate)
- ✅ Multiple payment methods (PhonePe, Razorpay, UPI, etc.)
- ✅ PhonePe AutoPay data
- ✅ Proper relationships
- ✅ Last 30 days of data

## 📖 Need More Info?

- **Gujarati Guide:** `PURCHASE_HISTORY_SUBSCRIPTIONS_SEEDER_GJ.md`
- **Full Details:** `SEEDER_IMPLEMENTATION_SUMMARY.md`
- **Data Structure:** `SEEDER_DATA_STRUCTURE.md`
- **Commands:** `SEEDER_COMMANDS.md`

## 🔄 Reset & Reseed

```bash
# Clear data
mysql -u root -p crafty_revenue -e "TRUNCATE TABLE purchase_history; TRUNCATE TABLE manage_subscriptions;"

# Reseed
php artisan db:seed --class=PurchaseHistoryAndSubscriptionsSeeder
```

## ⚠️ Troubleshooting

### Error: "user_data table ma koi users nathi"
**Fix:** Add users using Step 1 SQL above

### Error: "subscriptions table ma koi plans nathi"
**Fix:** Run `php artisan db:seed --class=SubscriptionPlansSeeder`

### Error: Database connection failed
**Fix:** Check `.env` file database credentials

---

**That's it! You're ready to go! 🎉**
