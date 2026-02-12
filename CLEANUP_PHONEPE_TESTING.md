# PhonePe Testing Files Cleanup

## Files to DELETE (Testing Only):

### Controllers:
1. `app/Http/Controllers/PhonePeSimplePaymentTestController.php`
2. `app/Http/Controllers/PhonePeAutoPayTestController.php`
3. `app/Http/Controllers/Api/PhonePePaymentApiController.php` (old version)
4. `app/Http/Controllers/Api/PhonePePaymentApiControllerOld.php`

### Views:
1. `resources/views/phonepe_simple_payment_test.blade.php`
2. `resources/views/phonepe/autopay_test.blade.php` (if exists)

### Models:
1. `app/Models/PhonePeAutoPayTestHistory.php` (testing table)

### Routes to REMOVE from `routes/web.php`:

```php
// Lines 383-405: Remove all testing routes
Route::get('/phonepe/autopay/test', ...)
Route::post('/phonepe/autopay/test/create', ...)
Route::get('/phonepe/autopay/test/status', ...)
Route::post('/phonepe/autopay/test/predebit', ...)
Route::post('/phonepe/autopay/test/debit', ...)
Route::get('/phonepe/autopay/test/list', ...)
Route::post('/phonepe/autopay/test/delete', ...)
Route::any('/phonepe/autopay/callback', ...)
Route::any('/phonepe/autopay/webhook', ...)
Route::get('/phonepe/simple-payment-test', ...)
Route::post('/phonepe/send-payment-request', ...)
Route::post('/phonepe/check-subscription-status', ...)
Route::post('/phonepe/send-predebit', ...)
Route::post('/phonepe/trigger-autodebit', ...)
Route::post('/phonepe/simulate-autodebit', ...)
Route::get('/phonepe/get-history', ...)
```

---

## Files to KEEP (Production):

### Controllers:
✅ `app/Http/Controllers/Api/PhonePeAutoPayController.php`
✅ `app/Http/Controllers/Api/PhonePePreDebitController.php`
✅ `app/Http/Controllers/Api/PhonePePaymentApiController2.php`
✅ `app/Http/Controllers/PhonePeDashboardController.php`
✅ `app/Http/Controllers/PhonePeTransactionController.php`
✅ `app/Http/Controllers/PhonePeNotificationController.php`
✅ `app/Http/Controllers/PhonePeWebhookController.php`

### Services:
✅ `app/Services/PhonePeAutoPayService.php`
✅ `app/Services/PhonePeTokenService.php`

### Models:
✅ `app/Models/PhonePeTransaction.php`
✅ `app/Models/PhonePeNotification.php`
✅ `app/Models/PhonePeAutoPayToken.php`
✅ `app/Models/PhonePeSubscription.php`

### Views (Admin):
✅ `resources/views/phonepe/dashboard.blade.php`
✅ `resources/views/phonepe/transactions/index.blade.php`
✅ `resources/views/phonepe/transactions/show.blade.php`
✅ `resources/views/phonepe/notifications/index.blade.php`
✅ `resources/views/phonepe/notifications/show.blade.php`

### Routes (Production):
✅ API Routes in `routes/api.php`:
```php
Route::any('phonepe/payment', [PhonePePaymentApiController2::class, 'payment']);
Route::post('phonepe/status/{transaction_id}', ...);
Route::any('payment/webhook', ...);
Route::prefix('phonepe/autopay')->group(...);
Route::prefix('phonepe/predebit')->group(...);
```

✅ Web Routes in `routes/web.php`:
```php
Route::get('/phonepe/dashboard', ...);
Route::get('/phonepe/transactions', ...);
Route::get('/phonepe/notifications', ...);
Route::any('/api/phonepe/webhook', ...);
Route::get('/payment-link/phonepe-callback', ...);
```

---

## Production API Endpoints:

### For Mobile App / Frontend:
```
POST /api/phonepe/payment
POST /api/phonepe/autopay/setup
POST /api/phonepe/autopay/cancel
GET  /api/phonepe/autopay/status/{subscriptionId}
POST /api/phonepe/autopay/redeem
POST /api/phonepe/predebit/send
```

### For Admin Panel:
```
GET  /phonepe/dashboard
GET  /phonepe/transactions
GET  /phonepe/transactions/{id}
GET  /phonepe/notifications
```

### For PhonePe Webhooks:
```
POST /api/phonepe/webhook
POST /payment-link/phonepe-callback
```

---

## Migration Note:

The testing controllers were using:
- `PhonePeAutoPayTestHistory` table (can be dropped)
- Test credentials hardcoded in controller

Production controllers use:
- `phonepe_transactions` table
- `phonepe_notifications` table
- `phonepe_autopay_tokens` table
- `phonepe_subscriptions` table
- Credentials from `payment_configurations` table

All production functionality is already working through the production controllers!
