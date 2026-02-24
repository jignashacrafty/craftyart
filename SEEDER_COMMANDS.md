# Quick Seeder Commands Reference

## Run Seeders in Order

### 1. First - Subscription Plans
```bash
php artisan db:seed --class=SubscriptionPlansSeeder
```

### 2. Second - Purchase History & User Subscriptions
```bash
php artisan db:seed --class=PurchaseHistoryAndSubscriptionsSeeder
```

## Verify Data

### Check Purchase History
```bash
mysql -u root -p crafty_revenue -e "SELECT COUNT(*) as total, SUM(CASE WHEN payment_status=1 THEN 1 ELSE 0 END) as success, SUM(CASE WHEN payment_status=0 THEN 1 ELSE 0 END) as failed FROM purchase_history;"
```

### Check User Subscriptions
```bash
mysql -u root -p crafty_revenue -e "SELECT COUNT(*) as total_subscriptions FROM manage_subscriptions;"
```

### Check Relationships
```bash
mysql -u root -p crafty_revenue -e "SELECT ph.user_id, ph.payment_method, ph.amount, ms.package_name FROM purchase_history ph LEFT JOIN manage_subscriptions ms ON ph.user_id = ms.user_id LIMIT 5;"
```

## Clean Data (if needed)

```bash
mysql -u root -p crafty_revenue -e "TRUNCATE TABLE purchase_history; TRUNCATE TABLE manage_subscriptions;"
```

## Full Reset & Reseed

```bash
# Clean tables
mysql -u root -p crafty_revenue -e "TRUNCATE TABLE purchase_history; TRUNCATE TABLE manage_subscriptions;"

# Reseed
php artisan db:seed --class=PurchaseHistoryAndSubscriptionsSeeder
```
