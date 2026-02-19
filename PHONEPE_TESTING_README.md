# PhonePe Simple Payment Testing - README

## 🎯 Quick Start

This is a complete testing suite for PhonePe AutoPay subscription payments with OAuth authentication.

### What's Included?

✅ **Web Interface** - Interactive testing page  
✅ **Postman Collection** - 7 API endpoints ready to test  
✅ **Complete Documentation** - Step-by-step guides  
✅ **Database Integration** - 3 tables for tracking  
✅ **OAuth Management** - Automatic token handling  

---

## 🚀 Get Started in 3 Steps

### Step 1: Open Web Interface

```
http://localhost/git_jignasha/craftyart/public/phonepe/simple-payment-test
```

Login as Admin, then use the form to send payment requests.

### Step 2: Import Postman Collection

1. Open Postman
2. Import `PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json`
3. Set variables:
   - `base_url`: Your app URL
   - `csrf_token`: Get from browser

### Step 3: Test the Flow

1. Send payment request → Get subscription ID
2. Approve on phone
3. Check status → Should be ACTIVE
4. Trigger payment (real) OR simulate (testing)
5. View history

---

## 📚 Documentation Files

| File | Purpose | When to Use |
|------|---------|-------------|
| **PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json** | Postman API collection | Import into Postman for API testing |
| **PHONEPE_SIMPLE_PAYMENT_TESTING_GUIDE.md** | Complete testing guide | Read for detailed instructions |
| **PHONEPE_API_QUICK_REFERENCE.md** | Quick API reference | Quick lookup for endpoints |
| **PHONEPE_TESTING_SUMMARY_GJ.md** | Gujarati summary | Gujarati speakers |
| **PHONEPE_TESTING_README.md** | This file | Start here |

---

## 🌐 Web Interface Features

**URL:** `/phonepe/simple-payment-test`

### Payment Request Form
- Enter UPI ID
- Enter amount (₹1 minimum)
- Enter mobile number
- Click "Send AutoPay Request"

### Transaction History Table
- View all transactions
- Real-time status updates
- Copy IDs with one click
- Action buttons for each transaction

### Action Buttons
- **🔍 Status** - Check subscription state
- **📧 Pre-Debit** - Verify ready for payment
- **💳 Debit** - Trigger real payment (⚠️ charges money!)
- **🧪 Simulate** - Test without real payment

---

## 🔧 API Endpoints

### 1. Send Payment Request
```http
POST /phonepe/send-payment-request
```
Creates AutoPay subscription, sends UPI notification to user.

### 2. Check Status
```http
POST /phonepe/check-subscription-status
```
Returns current subscription state (PENDING, ACTIVE, etc.)

### 3. Pre-Debit Check
```http
POST /phonepe/send-predebit
```
Verifies subscription is ready for payment.

### 4. Trigger Auto-Debit
```http
POST /phonepe/trigger-autodebit
```
⚠️ **Real payment** - Charges money from user's account!

### 5. Simulate Auto-Debit
```http
POST /phonepe/simulate-autodebit
```
🧪 **Testing only** - No real payment, safe for development.

### 6. Get History
```http
GET /phonepe/get-history
```
Returns last 50 transactions with all details.

---

## 📋 Testing Workflow

### For Real Payment Testing

```
1. Send Payment Request
   ↓ (Save merchant_subscription_id)
2. Approve on Phone
   ↓
3. Check Status
   ↓ (Verify state is ACTIVE)
4. Pre-Debit Check (optional)
   ↓
5. Trigger Auto-Debit
   ↓ (Real payment processed)
6. Get History
   ↓ (Verify transaction)
```

### For Development Testing

```
1. Send Payment Request
   ↓
2. Approve on Phone
   ↓
3. Check Status
   ↓
4. Simulate Auto-Debit
   ↓ (No real payment)
5. Get History
   ↓ (Verify simulation)
```

---

## 🔐 Authentication

### OAuth Credentials (Production)

```
Client ID: SU2512031928441979485878
Client Secret: 04652cf1-d98d-4f48-8ae8-0ecf60fac76f
Merchant User ID: M22EOXLUSO1LA
```

### Token Management
- Automatic generation
- 55-minute cache
- Auto-refresh on expiry

### API Authentication
- Admin login required
- CSRF token in headers
- Session-based

---

## 📊 Database Tables

### phonepe_transactions
Main transactions table tracking all subscriptions.

**Key Fields:**
- `merchant_subscription_id` - Your subscription ID
- `phonepe_order_id` - PhonePe's order ID
- `status` - Current status
- `is_autopay_active` - Boolean flag
- `autopay_count` - Number of successful payments

### phonepe_notifications
All notification events and their processing status.

**Key Fields:**
- `notification_type` - Type of event
- `event_type` - Specific event
- `status` - Event status
- `is_processed` - Processing flag

### phonepe_autopay_test_history
Legacy test table for historical tracking.

---

## 💡 Best Practices

### Testing
1. ✅ Always start with ₹1 amount
2. ✅ Use simulate for development
3. ✅ Check status before triggering debit
4. ✅ Save merchant_subscription_id from first response
5. ✅ Monitor logs for errors

### Production
1. ✅ Test complete flow on staging first
2. ✅ Verify all database tables
3. ✅ Set up monitoring
4. ✅ Document credentials securely
5. ✅ Train support team

### Security
1. ✅ Keep credentials secure
2. ✅ Use HTTPS in production
3. ✅ Validate all inputs
4. ✅ Log all transactions
5. ✅ Monitor for fraud

---

## 🐛 Troubleshooting

### Common Issues

**"OAuth Token Generation Failed"**
- Check internet connection
- Verify credentials
- Check PhonePe API status

**"Subscription must be ACTIVE"**
- User hasn't approved mandate yet
- Check status with status API
- Wait for user approval

**"CSRF token mismatch"**
- Get fresh token from browser
- Update Postman variable
- Ensure logged in

**"Unauthorized" or "403 Forbidden"**
- Login as Admin
- Check session is active
- Verify middleware

### Where to Look

**Laravel Logs:**
```
storage/logs/laravel.log
```

**Database:**
```sql
SELECT * FROM phonepe_transactions ORDER BY created_at DESC LIMIT 10;
SELECT * FROM phonepe_notifications ORDER BY created_at DESC LIMIT 10;
```

**Browser Console:**
```
F12 → Console → Check for JavaScript errors
```

---

## 📱 Testing on Real Device

### Prerequisites
- Real UPI ID (e.g., yourname@okaxis)
- UPI app installed on phone
- Active bank account linked

### Steps
1. Use your real UPI ID in request
2. Check phone for notification
3. Approve mandate in UPI app
4. Test with ₹1 first
5. Verify in bank statement

### Test UPI IDs
```
vrajsurani606@okaxis
yourname@paytm
yourname@ybl
yourname@oksbi
```

---

## 🎨 Postman Setup

### Import Collection
1. Open Postman
2. Click "Import"
3. Select `PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json`
4. Collection imported with 7 endpoints

### Set Variables
```
base_url = http://localhost/git_jignasha/craftyart/public
csrf_token = YOUR_TOKEN_FROM_BROWSER
```

### Get CSRF Token

**Method 1 - Browser Console:**
```javascript
document.querySelector('meta[name="csrf-token"]').content
```

**Method 2 - Cookies:**
```
DevTools → Application → Cookies → XSRF-TOKEN
```

**Method 3 - Page Source:**
```html
<meta name="csrf-token" content="YOUR_TOKEN">
```

---

## 📖 Documentation Guide

### For Quick Testing
→ Read: **PHONEPE_API_QUICK_REFERENCE.md**

### For Complete Understanding
→ Read: **PHONEPE_SIMPLE_PAYMENT_TESTING_GUIDE.md**

### For Postman Testing
→ Import: **PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json**

### For Gujarati Speakers
→ Read: **PHONEPE_TESTING_SUMMARY_GJ.md**

---

## ✅ Pre-Launch Checklist

Before going to production:

- [ ] Test complete flow with real UPI
- [ ] Verify all database tables working
- [ ] Check webhook handling (if applicable)
- [ ] Test error scenarios
- [ ] Review security measures
- [ ] Set up monitoring and alerts
- [ ] Document production credentials
- [ ] Train support team
- [ ] Create backup plan
- [ ] Test rollback procedure

---

## 🎯 Success Criteria

Your testing is successful when:

✅ Payment request sent successfully  
✅ User receives UPI notification  
✅ User can approve mandate  
✅ Status shows ACTIVE  
✅ Auto-debit triggers successfully  
✅ Database records updated correctly  
✅ History shows all transactions  
✅ Notifications created properly  

---

## 📞 Support

### Resources
- **Laravel Logs:** `storage/logs/laravel.log`
- **PhonePe Docs:** https://developer.phonepe.com/
- **Test Page:** `/phonepe/simple-payment-test`

### Contact
For issues or questions, check:
1. Laravel logs first
2. Database tables
3. PhonePe documentation
4. This documentation

---

## 🎉 You're Ready!

Everything is set up and ready to test. Choose your preferred method:

**Option 1: Web Interface** (Easiest)
```
Open: /phonepe/simple-payment-test
```

**Option 2: Postman** (For API testing)
```
Import: PHONEPE_SIMPLE_PAYMENT_POSTMAN_COLLECTION.json
```

**Option 3: cURL** (For command line)
```
See: PHONEPE_API_QUICK_REFERENCE.md
```

---

**Version:** 1.0  
**Last Updated:** February 17, 2026  
**Status:** ✅ Production Ready

**Happy Testing! 🚀**
