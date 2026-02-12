# ✅ Production Ready - PhonePe AutoPay System

## Overview

Complete PhonePe AutoPay implementation integrated into existing PlanController for recurring subscription payments.

## Features Implemented

### 1. PhonePe AutoPay Integration
- ✅ AutoPay mandate creation
- ✅ Recurring payment processing
- ✅ Pre-debit notifications (24 hours before payment)
- ✅ Webhook handling for payment updates
- ✅ Automatic subscription management

### 2. Scheduled Tasks
- **Pre-debit Notifications:** Daily at 10:00 AM
- **Recurring Payments:** Daily at 12:00 PM
- **Pending Payment Check:** Every 5 minutes

### 3. Database Tables
- `phonepe_transactions` - Transaction records
- `phonepe_notifications` - Notification logs
- `phonepe_pre_debit_notifications` - Pre-debit notification tracking
- `phonepe_subscriptions` - Subscription management
- `phonepe_tokens` - OAuth tokens
- `phonepe_autopay_tokens` - AutoPay tokens

## Production Setup

### Step 1: Configure PhonePe Credentials

Update in `payment_configurations` table:

```sql
UPDATE payment_configurations 
SET 
    credentials = JSON_OBJECT(
        'client_id', 'YOUR_PRODUCTION_CLIENT_ID',
        'client_secret', 'YOUR_PRODUCTION_CLIENT_SECRET',
        'merchant_user_id', 'YOUR_MERCHANT_USER_ID',
        'api_url', 'https://api.phonepe.com/apis/hermes',
        'oauth_url', 'https://api.phonepe.com/apis/identity-manager/v1/oauth/token'
    ),
    webhook_url = 'https://your-domain.com/phonepe/autopay/webhook',
    callback_url = 'https://your-domain.com/phonepe/autopay/callback',
    is_active = 1,
    environment = 'production'
WHERE gateway = 'PhonePe';
```

### Step 2: Update Environment Variables

```env
PHONEPE_CLIENT_ID=your_production_client_id
PHONEPE_CLIENT_SECRET=your_production_client_secret
PHONEPE_MERCHANT_USER_ID=your_merchant_user_id
```

### Step 3: Clear Cache

```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

### Step 4: Configure Cron Jobs

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

### Step 5: Start Queue Workers

```bash
php artisan queue:work
```

## API Endpoints

### PhonePe AutoPay
```
POST /api/phonepe/autopay/setup                 - Setup AutoPay subscription
POST /api/phonepe/autopay/cancel                - Cancel subscription
GET  /api/phonepe/autopay/status/{id}           - Get subscription status
POST /api/phonepe/autopay/redeem                - Trigger manual redemption
```

### PhonePe Pre-Debit
```
POST /api/phonepe/predebit/send                 - Send pre-debit notification
```

### PhonePe Webhooks
```
POST /phonepe/autopay/webhook                   - PhonePe webhook callback
POST /phonepe/autopay/callback                  - Payment callback
```

## Commands

```bash
# Send pre-debit notifications
php artisan phonepe:send-predebit-notifications

# Process recurring payments
php artisan phonepe:process-recurring-payments

# Check pending payments
php artisan phonepe:check-pending
```

## Monitoring

### Check Logs
```bash
tail -f storage/logs/laravel.log | grep PhonePe
```

### Check Transactions
```sql
SELECT * FROM phonepe_transactions 
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
ORDER BY created_at DESC;
```

### Check Queue Jobs
```bash
php artisan queue:work
php artisan queue:failed
```

## Files Structure

### Controllers
- `app/Http/Controllers/Api/PhonePeAutoPayController.php` - AutoPay API
- `app/Http/Controllers/Api/PhonePePreDebitController.php` - Pre-debit notifications
- `app/Http/Controllers/PhonePeWebhookController.php` - Webhook handling
- `app/Http/Controllers/PhonePeTransactionController.php` - Transaction management
- `app/Http/Controllers/PhonePeNotificationController.php` - Notification management
- `app/Http/Controllers/PhonePeDashboardController.php` - Dashboard

### Services
- `app/Services/PhonePeAutoPayService.php` - Core PhonePe service
- `app/Services/PhonePeTokenService.php` - Token management
- `app/Services/WhatsAppService.php` - WhatsApp notifications

### Models
- `app/Models/PhonePeTransaction.php`
- `app/Models/PhonePeNotification.php`
- `app/Models/PhonePePreDebitNotification.php`
- `app/Models/PhonePeSubscription.php`
- `app/Models/PhonePeToken.php`
- `app/Models/PhonePeAutoPayToken.php`

### Commands
- `app/Console/Commands/SendPhonePePreDebitNotifications.php`
- `app/Console/Commands/ProcessPhonePeRecurringPayments.php`
- `app/Console/Commands/ProcessPhonePeAutoPayments.php`

### Jobs
- `app/Jobs/ProcessPhonePePreDebitNotification.php`
- `app/Jobs/ProcessPhonePeRecurringPayment.php`

### Views
- `resources/views/phonepe/dashboard.blade.php`
- `resources/views/phonepe/transactions/index.blade.php`
- `resources/views/phonepe/transactions/show.blade.php`
- `resources/views/phonepe/notifications/index.blade.php`
- `resources/views/phonepe/notifications/show.blade.php`

## Security

- All credentials encrypted in database
- HTTPS required for webhooks
- CSRF protection enabled
- Rate limiting configured
- Webhook signature verification
- OAuth token caching

## Testing

### Test Simple Payment (Admin Only)

URL: `https://your-domain.com/phonepe/simple-payment-test`

This page allows testing PhonePe simple payments (not AutoPay):
- Immediate payment requests
- UPI Intent or UPI Collect modes
- Real-time API testing
- Response debugging

**Features:**
- Test different payment amounts
- Test with mobile number
- Optional UPI ID
- Choose payment mode
- View full API response

### Test AutoPay Setup
```bash
# Via API
curl -X POST https://your-domain.com/api/phonepe/autopay/setup \
  -H "Content-Type: application/json" \
  -d '{"plan_id": 1, "amount": 999}'
```

### Test Pre-Debit Notification
```bash
php artisan phonepe:send-predebit-notifications
```

### Test Recurring Payment
```bash
php artisan phonepe:process-recurring-payments
```

## Troubleshooting

### Issue: Payment URL Not Generated
**Solution:** Verify production credentials in database

### Issue: Webhook Not Received
**Solution:** Check webhook URL is publicly accessible with HTTPS

### Issue: Queue Jobs Not Processing
**Solution:** Restart queue workers: `php artisan queue:restart`

## Deployment Checklist

```
□ PhonePe production credentials configured
□ Environment variables updated
□ Cache cleared
□ Cron jobs configured
□ Queue workers running
□ Webhooks tested
□ Logs monitored
□ Database indexes added
□ SSL certificate valid
□ Firewall configured
```

## Support

For issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify credentials in database
3. Test webhook URL accessibility
4. Check queue worker status

---

**Version:** 1.0.0  
**Last Updated:** February 11, 2026  
**Status:** ✅ Production Ready
