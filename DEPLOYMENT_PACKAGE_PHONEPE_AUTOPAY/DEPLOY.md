# 🚀 Deployment Guide - PhonePe AutoPay Integration

## 📋 Feature Summary

**Feature:** PhonePe AutoPay (Recurring Subscriptions) with Pre-Debit Notifications  
**Version:** 2.0  
**Date:** February 11, 2026  
**Database:** mysql (main application database)

---

## 📦 Files Summary

### CHANGES Folder: 2 files
- **Routes:** 1 file (api.php - added PhonePe routes)
- **Views:** 1 file (header.blade.php - added PhonePe menu)

### NEW_FILES Folder: 27 files
- **Controllers:** 6 files
- **Services:** 2 files
- **Models:** 6 files
- **Views:** 5 files
- **Jobs:** 1 file
- **Migrations:** 7 files

**Total Files:** 29 files

---

## ⚠️ Pre-Deployment Checklist

- [ ] Backup database
- [ ] Backup current codebase
- [ ] Verify PhonePe production credentials
- [ ] Note down current application version
- [ ] Ensure server has PHP 8.0+
- [ ] Verify Laravel version compatibility

---

## 📝 Step 1: Backup

### 1.1 Backup Database
```bash
# Navigate to project root
cd /path/to/craftyart

# Backup database
mysqldump -u root -p crafty_db > backup_before_phonepe_$(date +%Y%m%d_%H%M%S).sql

# Verify backup created
ls -lh backup_before_phonepe_*.sql
```

### 1.2 Backup Current Code
```bash
# Create backup of current codebase
tar -czf backup_code_$(date +%Y%m%d_%H%M%S).tar.gz \
  app/ \
  database/ \
  resources/ \
  routes/ \
  config/ \
  public/

# Verify backup
ls -lh backup_code_*.tar.gz
```

---

## 📤 Step 2: Upload Deployment Package

### 2.1 Upload via FTP/SFTP
```bash
# Upload entire DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY folder to server
# Location: /home/your-user/deployments/
```

### 2.2 Or Upload via SCP
```bash
scp -r DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY user@server:/home/your-user/deployments/
```

### 2.3 SSH to Server
```bash
ssh user@your-server.com
cd /path/to/craftyart
```

---

## 🔄 Step 3: Deploy CHANGES Files

### 3.1 Routes File (Modified)
```bash
# Backup current routes
cp routes/api.php routes/api.php.backup

# Deploy new routes file
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/CHANGES/api.php routes/

# Verify
diff routes/api.php.backup routes/api.php
```

**What Changed:**
- Commented out unused `VerificationController` routes (lines 184-185)
- Added PhonePe AutoPay API routes (`/api/phonepe/autopay/*`)
- Added PhonePe Pre-Debit API routes (`/api/phonepe/predebit/*`)
- This fixes route loading issues

### 3.2 Header View File (Modified)
```bash
# Backup current header
cp resources/views/layouts/header.blade.php resources/views/layouts/header.blade.php.backup

# Deploy new header file
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/CHANGES/header.blade.php resources/views/layouts/

# Verify
diff resources/views/layouts/header.blade.php.backup resources/views/layouts/header.blade.php
```

**What Changed:**
- Added PhonePe AutoPay menu section in admin sidebar
- Menu includes: Dashboard, Transactions, Notifications
- Menu icon: Mobile phone icon (fa-mobile)
- Menu color: Green (#1abc9c)

---

## 📁 Step 4: Deploy NEW_FILES

### 4.1 Controllers
```bash
# Create directories if not exist
mkdir -p app/Http/Controllers/Api

# Copy API Controllers
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeAutoPayController.php \
   app/Http/Controllers/Api/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePePreDebitController.php \
   app/Http/Controllers/Api/

# Copy Admin Controllers
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeDashboardController.php \
   app/Http/Controllers/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeTransactionController.php \
   app/Http/Controllers/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeNotificationController.php \
   app/Http/Controllers/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeWebhookController.php \
   app/Http/Controllers/

# Verify
ls -la app/Http/Controllers/Api/PhonePe*.php
ls -la app/Http/Controllers/PhonePe*.php
```

### 4.2 Services
```bash
# Create services directory if not exist
mkdir -p app/Services

# Copy PhonePe Services
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeAutoPayService.php \
   app/Services/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeTokenService.php \
   app/Services/

# Verify
ls -la app/Services/PhonePe*.php
```

### 4.3 Models
```bash
# Copy PhonePe Models
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeTransaction.php \
   app/Models/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeNotification.php \
   app/Models/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeAutoPayToken.php \
   app/Models/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeSubscription.php \
   app/Models/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeAutoPayTransaction.php \
   app/Models/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/PhonePeToken.php \
   app/Models/

# Verify
ls -la app/Models/PhonePe*.php
```

### 4.4 Views (Admin Dashboard)
```bash
# Create phonepe views directory
mkdir -p resources/views/phonepe/transactions
mkdir -p resources/views/phonepe/notifications

# Copy Dashboard View
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/dashboard.blade.php \
   resources/views/phonepe/

# Copy Transaction Views
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/transactions_index.blade.php \
   resources/views/phonepe/transactions/index.blade.php

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/transactions_show.blade.php \
   resources/views/phonepe/transactions/show.blade.php

# Copy Notification Views
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/notifications_index.blade.php \
   resources/views/phonepe/notifications/index.blade.php

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/notifications_show.blade.php \
   resources/views/phonepe/notifications/show.blade.php

# Verify
ls -la resources/views/phonepe/
ls -la resources/views/phonepe/transactions/
ls -la resources/views/phonepe/notifications/
```

### 4.5 Jobs (Cron Jobs for Recurring Payments)
```bash
# Copy PhonePe Jobs
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/ProcessPhonePeRecurringPayment.php \
   app/Jobs/

# Verify
ls -la app/Jobs/ProcessPhonePe*.php
```

### 4.6 Migrations
```bash
# Copy all PhonePe migrations
cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_02_180000_create_phonepe_tokens_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_02_180001_create_phonepe_subscriptions_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_02_180002_create_phonepe_autopay_transactions_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_02_180003_create_phonepe_pre_debit_notifications_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_09_165600_create_phonepe_autopay_tokens_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_10_120000_create_phonepe_transactions_table.php \
   database/migrations/

cp ~/deployments/DEPLOYMENT_PACKAGE_PHONEPE_AUTOPAY/NEW_FILES/2026_02_10_120001_create_phonepe_notifications_table.php \
   database/migrations/

# Verify
ls -la database/migrations/*phonepe*.php
```

---

## 🗄️ Step 5: Run Database Migrations

### 5.1 Check Migration Status
```bash
php artisan migrate:status --database=mysql
```

### 5.2 Run Migrations
```bash
# Run PhonePe migrations
php artisan migrate --database=mysql

# Expected output:
# Migrating: 2026_02_02_180000_create_phonepe_tokens_table
# Migrated:  2026_02_02_180000_create_phonepe_tokens_table (XX.XXms)
# ... (7 migrations total)
```

### 5.3 Verify Tables Created
```bash
php artisan tinker
```

```php
// In tinker
DB::connection('mysql')->select("SHOW TABLES LIKE 'phonepe%'");
// Should show 7 tables:
// - phonepe_tokens
// - phonepe_subscriptions
// - phonepe_autopay_transactions
// - phonepe_pre_debit_notifications
// - phonepe_autopay_tokens
// - phonepe_transactions
// - phonepe_notifications
exit
```

---

## ⚙️ Step 6: Configure PhonePe Credentials

### 6.1 Update Payment Configuration
```bash
php artisan tinker
```

```php
// In tinker
$config = \App\Models\PaymentConfiguration::where('gateway', 'PhonePe')->first();

if (!$config) {
    // Create new configuration
    $config = \App\Models\PaymentConfiguration::create([
        'payment_scope' => 'NATIONAL',
        'gateway' => 'phonepe',
        'credentials' => [
            'client_id' => 'YOUR_PRODUCTION_CLIENT_ID',
            'client_secret' => 'YOUR_PRODUCTION_CLIENT_SECRET',
            'merchant_id' => 'YOUR_MERCHANT_ID',
            'merchant_user_id' => 'YOUR_MERCHANT_ID',
            'client_version' => '1',
            'environment' => 'production',
            'webhook_url' => 'https://your-domain.com/api/phonepe/webhook'
        ],
        'payment_types' => ['subscription', 'autopay', 'recurring', 'one_time'],
        'is_active' => true
    ]);
} else {
    // Update existing configuration
    $credentials = $config->credentials;
    $credentials['merchant_id'] = 'YOUR_MERCHANT_ID';
    $credentials['environment'] = 'production';
    $config->credentials = $credentials;
    $config->save();
}

echo "✅ Configuration updated!\n";
exit
```

### 6.2 Update .env File (Optional)
```bash
# Edit .env file
nano .env

# Add/Update these lines:
PHONEPE_MERCHANT_ID=YOUR_MERCHANT_ID
PHONEPE_CLIENT_ID=YOUR_CLIENT_ID
PHONEPE_CLIENT_SECRET=YOUR_CLIENT_SECRET
PHONEPE_CLIENT_VERSION=1
PHONEPE_ENV=production
PHONEPE_CALLBACK_URL=https://your-domain.com/payment-link/phonepe-callback

# Save and exit (Ctrl+X, Y, Enter)
```

---

## 🧹 Step 7: Clear All Caches

```bash
# Clear application cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Verify
php artisan route:list | grep phonepe
```

---

## 🔔 Step 8: Configure PhonePe Webhook

### 8.1 Login to PhonePe Merchant Dashboard
```
URL: https://business.phonepe.com/
```

### 8.2 Add Webhook URL
```
Webhook URL: https://your-domain.com/api/phonepe/webhook
Method: POST
Events: All subscription events
```

### 8.3 Test Webhook
```bash
# Test webhook endpoint
curl -X POST "https://your-domain.com/api/phonepe/webhook" \
  -H "Content-Type: application/json" \
  -d '{"test": "webhook"}'

# Should return: {"success":true,"message":"Webhook received and processed"}
```

---

## ✅ Step 9: Test URLs

### 9.1 Admin Dashboard URLs
```
Dashboard: https://your-domain.com/phonepe/dashboard
Transactions: https://your-domain.com/phonepe/transactions
Notifications: https://your-domain.com/phonepe/notifications
```

### 9.2 API Endpoints
```bash
# Test AutoPay Setup
curl -X POST "https://your-domain.com/api/phonepe/autopay/setup" \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "test_user_uid",
    "plan_id": "1",
    "amount": 1
  }'

# Expected: {"success":true,"message":"Subscription setup initiated successfully",...}
```

### 9.3 Check Database
```sql
-- Check if tables exist
SHOW TABLES LIKE 'phonepe%';

-- Check payment configuration
SELECT * FROM payment_configurations WHERE gateway = 'PhonePe';

-- Check if any transactions created
SELECT COUNT(*) FROM phonepe_transactions;
```

---

## 🔄 Step 10: Setup Cron Jobs (For Recurring Payments)

### 10.1 Add to Crontab
```bash
# Edit crontab
crontab -e

# Add this line (runs every minute)
* * * * * cd /path/to/craftyart && php artisan schedule:run >> /dev/null 2>&1
```

### 10.2 Verify Cron Job
```bash
# Check crontab
crontab -l

# Test schedule
php artisan schedule:list
```

---

## 🚨 Rollback Instructions

### If Something Goes Wrong:

#### Rollback Database
```bash
# Rollback last 7 migrations
php artisan migrate:rollback --step=7 --database=mysql

# Or restore from backup
mysql -u root -p crafty_db < backup_before_phonepe_YYYYMMDD_HHMMSS.sql
```

#### Rollback Code
```bash
# Restore from backup
tar -xzf backup_code_YYYYMMDD_HHMMSS.tar.gz

# Or manually remove files
rm -f app/Http/Controllers/Api/PhonePeAutoPayController.php
rm -f app/Http/Controllers/Api/PhonePePreDebitController.php
rm -f app/Http/Controllers/PhonePe*.php
rm -f app/Services/PhonePe*.php
rm -f app/Models/PhonePe*.php
rm -rf resources/views/phonepe/
rm -f database/migrations/*phonepe*.php

# Restore routes
cp routes/api.php.backup routes/api.php

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

---

## 🐛 Common Issues & Solutions

### Issue 1: OAuth 401 Error
**Symptom:** `PhonePe OAuth failed: {"success":false,"code":"401"}`

**Solution:**
```bash
# Check credentials in database
php artisan tinker
$config = \App\Models\PaymentConfiguration::where('gateway', 'PhonePe')->first();
print_r($config->credentials);

# Verify these fields exist:
# - client_id
# - client_secret
# - merchant_id
# - environment: "production"
```

### Issue 2: Routes Not Found (404)
**Symptom:** API returns 404

**Solution:**
```bash
# Clear route cache
php artisan route:clear
php artisan route:cache

# Verify routes registered
php artisan route:list | grep phonepe
```

### Issue 3: Migrations Already Exist
**Symptom:** `Migration already exists`

**Solution:**
```bash
# Check migration status
php artisan migrate:status --database=mysql

# If already migrated, skip this step
```

### Issue 4: Webhook Not Received
**Symptom:** No webhook notifications in database

**Solution:**
```bash
# Check webhook URL in PhonePe dashboard
# Verify URL is publicly accessible
# Check logs
tail -f storage/logs/laravel.log | grep -i webhook
```

---

## 📊 Monitoring & Logs

### Check Logs
```bash
# Real-time log monitoring
tail -f storage/logs/laravel.log

# Search for PhonePe logs
grep "PhonePe" storage/logs/laravel.log

# Search for errors
grep "ERROR" storage/logs/laravel.log | grep -i phonepe
```

### Database Queries
```sql
-- Recent orders
SELECT * FROM orders WHERE razorpay_order_id LIKE 'PHONEPE%' ORDER BY id DESC LIMIT 10;

-- Active subscriptions
SELECT * FROM phonepe_subscriptions WHERE status = 'ACTIVE' ORDER BY id DESC;

-- Recent webhooks
SELECT * FROM phonepe_notifications ORDER BY id DESC LIMIT 10;

-- Transaction summary
SELECT 
    status, 
    COUNT(*) as count, 
    SUM(amount) as total_amount 
FROM phonepe_transactions 
GROUP BY status;
```

---

## ✅ Post-Deployment Checklist

- [ ] All files deployed successfully
- [ ] Migrations ran without errors
- [ ] PhonePe credentials configured
- [ ] Webhook URL configured in PhonePe dashboard
- [ ] Admin dashboard accessible
- [ ] API endpoints responding
- [ ] Test transaction completed successfully
- [ ] Cron jobs configured
- [ ] Logs monitoring setup
- [ ] Backup verified

---

## 📞 Support

### Log Files:
```
Laravel Logs: storage/logs/laravel.log
PhonePe Logs: grep "PhonePe" storage/logs/laravel.log
```

### Database Tables:
```
- orders (existing, modified for PhonePe)
- phonepe_tokens
- phonepe_subscriptions
- phonepe_autopay_transactions
- phonepe_pre_debit_notifications
- phonepe_autopay_tokens
- phonepe_transactions
- phonepe_notifications
```

### Admin URLs:
```
Dashboard: /phonepe/dashboard
Transactions: /phonepe/transactions
Notifications: /phonepe/notifications
```

---

## 🎉 Deployment Complete!

જો બધું સફળતાપૂર્વક થયું હોય તો:

✅ PhonePe AutoPay integration is now live!  
✅ Users can setup recurring subscriptions  
✅ Pre-debit notifications will be sent automatically  
✅ Admin can monitor all transactions  
✅ Webhooks are being received and processed  

---

**Deployment Date:** [Fill in deployment date]  
**Deployed By:** [Fill in your name]  
**Version:** 2.0  
**Status:** ✅ Production Ready

---

**End of Deployment Guide**
