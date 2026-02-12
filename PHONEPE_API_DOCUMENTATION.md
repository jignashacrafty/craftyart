# PhonePe Payment Integration - API Documentation

## 📋 Table of Contents
1. [Overview](#overview)
2. [Authentication](#authentication)
3. [Payment Flow](#payment-flow)
4. [API Endpoints](#api-endpoints)
5. [Webhook Integration](#webhook-integration)
6. [Order Creation](#order-creation)
7. [Error Handling](#error-handling)
8. [Testing](#testing)

---

## 🎯 Overview

This API provides PhonePe payment integration with support for:
- ✅ One-time payments
- ✅ UPI AutoPay (Recurring subscriptions)
- ✅ Pre-debit notifications
- ✅ Automatic order creation
- ✅ Webhook notifications

**Base URL**: `https://your-domain.com/api`

---

## 🔐 Authentication

All API requests require authentication. Include user credentials in the request body.

```json
{
  "user_id": "user_unique_id",
  "email": "user@example.com"
}
```

---

## 💳 Payment Flow

### One-Time Payment Flow:
```
1. Frontend → POST /api/phonepe/payment
2. Backend → Creates Order in database
3. Backend → Calls PhonePe API
4. Backend → Returns payment URL
5. User → Completes payment on PhonePe
6. PhonePe → Sends webhook to /api/phonepe/webhook
7. Backend → Updates Order status
8. Frontend → Check order status
```

### AutoPay Subscription Flow:
```
1. Frontend → POST /api/phonepe/autopay/setup
2. Backend → Creates Order & Subscription
3. Backend → Returns redirect URL
4. User → Approves mandate in UPI app
5. PhonePe → Sends webhook (ACTIVE status)
6. Backend → Updates subscription status
7. Monthly → PhonePe auto-debits amount
8. Backend → Receives webhook & creates new order
```

---

## 📡 API Endpoints

### 1. One-Time Payment

**Endpoint**: `POST /api/phonepe/payment`

**Description**: Create a one-time payment request

**Request Body**:
```json
{
  "p": "1",              // Plan ID or subscription ID
  "name": "John Doe",
  "number": "9876543210",
  "email": "user@example.com",
  "code": ""             // Optional promo code
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Loaded!",
  "data": {
    "data": {
      "merchantOrderID": "PHONEPE_1707654321",
      "transactionId": "TXN123456",
      "instrumentResponse": {
        "redirectInfo": {
          "url": "https://phonepe.com/payment/..."
        }
      }
    }
  }
}
```

**Order Creation**: ✅ Order is automatically created in `orders` table with status `pending`

---

### 2. Setup AutoPay Subscription

**Endpoint**: `POST /api/phonepe/autopay/setup`

**Description**: Setup recurring UPI AutoPay subscription

**Request Body**:
```json
{
  "user_id": "user_unique_id",
  "plan_id": "plan_123",
  "amount": 99,
  "upi": "user@okaxis",      // Optional
  "target_app": "PHONEPE"    // Optional
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Subscription setup initiated successfully",
  "data": {
    "merchant_order_id": "MO_SETUP_abc123",
    "merchant_subscription_id": "MS_xyz789",
    "phonepe_order_id": "OMO123456",
    "redirect_url": "https://phonepe.com/mandate/...",
    "state": "PENDING",
    "expire_at": 1707654321000
  }
}
```

**Order Creation**: ✅ Order is automatically created in `orders` table
**Subscription Creation**: ✅ Subscription record created in `phonepe_subscriptions` table

**What happens next**:
1. User clicks `redirect_url` and approves mandate in UPI app
2. PhonePe sends webhook with `ACTIVE` status
3. Subscription status updated to `ACTIVE`
4. Monthly auto-debit starts

---

### 3. Cancel Subscription

**Endpoint**: `POST /api/phonepe/autopay/cancel`

**Description**: Cancel an active AutoPay subscription

**Request Body**:
```json
{
  "merchant_subscription_id": "MS_xyz789"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Subscription cancelled successfully"
}
```

---

### 4. Check Subscription Status

**Endpoint**: `GET /api/phonepe/autopay/status/{merchantSubscriptionId}`

**Description**: Get current status of a subscription

**Success Response** (200):
```json
{
  "success": true,
  "data": {
    "local_status": "ACTIVE",
    "phonepe_status": "ACTIVE",
    "details": {
      "merchantSubscriptionId": "MS_xyz789",
      "subscriptionId": "OMS123456",
      "state": "ACTIVE",
      "frequency": "MONTHLY",
      "maxAmount": 9900,
      "currency": "INR"
    }
  }
}
```

---

### 5. Trigger Manual Payment (AutoPay)

**Endpoint**: `POST /api/phonepe/autopay/redeem`

**Description**: Manually trigger a payment for active subscription

**Request Body**:
```json
{
  "merchant_subscription_id": "MS_xyz789"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Manual redemption triggered",
  "data": {
    "merchant_order_id": "MO_MANUAL_abc123",
    "phonepe_order_id": "OMO789456"
  }
}
```

**Order Creation**: ✅ New order created for this payment

---

### 6. Send Pre-Debit Notification

**Endpoint**: `POST /api/phonepe/predebit/send`

**Description**: Send pre-debit notification to user (24 hours before payment)

**Request Body**:
```json
{
  "merchant_subscription_id": "MS_xyz789",
  "amount": 99,
  "scheduled_date": "2024-02-15"
}
```

**Success Response** (200):
```json
{
  "success": true,
  "message": "Pre-debit notification sent successfully",
  "data": {
    "notification_id": "NOTIF_123",
    "scheduled_payment_date": "2024-02-15",
    "amount": 99
  }
}
```

**Note**: Pre-debit SMS is sent by user's bank, not by PhonePe directly

---

### 7. Check Payment Status

**Endpoint**: `POST /api/phonepe/status/{transaction_id}`

**Description**: Check status of a payment transaction

**Success Response** (200):
```json
{
  "success": true,
  "message": "Status Fetched",
  "status": {
    "code": "PAYMENT_SUCCESS",
    "state": "COMPLETED",
    "transactionId": "TXN123456",
    "amount": 9900
  }
}
```

---

### 8. Refund Payment

**Endpoint**: `GET /api/phonepe/refund`

**Description**: Initiate refund for a completed payment

**Query Parameters**:
- `merchant_order_id`: Merchant order ID
- `amount`: Refund amount

**Success Response** (200):
```json
{
  "success": true,
  "message": "Refund initiated",
  "data": {
    "merchantRefundId": "REFUND_123",
    "originalMerchantOrderId": "PHONEPE_1707654321",
    "amount": 9900,
    "state": "PENDING"
  }
}
```

---

## 🔔 Webhook Integration

**Webhook URL**: `POST /api/phonepe/webhook`

**Description**: PhonePe sends webhook notifications for payment events

**Webhook Events**:
- `SUBSCRIPTION_SETUP_COMPLETED` - Mandate approved
- `SUBSCRIPTION_SETUP_FAILED` - Mandate rejected
- `SUBSCRIPTION_REDEMPTION_COMPLETED` - Payment successful
- `SUBSCRIPTION_REDEMPTION_FAILED` - Payment failed
- `PRE_DEBIT_NOTIFICATION` - Pre-debit notification sent

**Webhook Payload Example**:
```json
{
  "merchantOrderId": "MO_SETUP_abc123",
  "merchantSubscriptionId": "MS_xyz789",
  "orderId": "OMO123456",
  "transactionId": "TXN789456",
  "state": "ACTIVE",
  "amount": 9900,
  "event": "SUBSCRIPTION_SETUP_COMPLETED",
  "paymentMethod": "UPI"
}
```

**What Backend Does**:
1. ✅ Receives webhook
2. ✅ Creates record in `phonepe_notifications` table
3. ✅ Updates `phonepe_transactions` table
4. ✅ Updates `phonepe_subscriptions` table
5. ✅ Creates/Updates `orders` table
6. ✅ Returns success response to PhonePe

**Webhook Response**:
```json
{
  "success": true,
  "message": "Webhook received and processed",
  "notification_id": 123
}
```

---

## 📦 Order Creation

### When Orders Are Created:

1. **One-Time Payment**:
   - Order created immediately when `/api/phonepe/payment` is called
   - Status: `pending`
   - Updated to `completed` when webhook received

2. **AutoPay Setup**:
   - Order created when `/api/phonepe/autopay/setup` is called
   - Status: `pending`
   - Updated to `completed` when first payment succeeds

3. **Monthly AutoPay**:
   - New order created automatically each month
   - Triggered by PhonePe webhook
   - Status: `completed` (if payment successful)

### Order Table Fields:
```php
[
    'user_id' => 'user_unique_id',
    'plan_id' => 'plan_123',
    'crafty_id' => 'CRAFTY_123456',  // Auto-generated
    'amount' => 99.00,
    'currency' => 'INR',
    'status' => 'pending',  // or 'completed', 'failed'
    'type' => 'new_sub',    // or 'template', 'video', etc.
    'razorpay_order_id' => 'PHONEPE_1707654321',  // PhonePe order ID
    'created_at' => '2024-02-11 10:30:00',
    'updated_at' => '2024-02-11 10:35:00'
]
```

---

## ⚠️ Error Handling

### Common Error Responses:

**Invalid User** (404):
```json
{
  "success": false,
  "message": "User not found"
}
```

**Payment Failed** (400):
```json
{
  "success": false,
  "message": "Payment request failed",
  "error": {
    "code": "PAYMENT_DECLINED",
    "message": "Insufficient balance"
  }
}
```

**Subscription Not Active** (400):
```json
{
  "success": false,
  "message": "Subscription must be ACTIVE to trigger auto-debit. Current state: PENDING"
}
```

**Authorization Failed** (400):
```json
{
  "success": false,
  "message": "PhonePe Authorization Failed",
  "error": "Authorization failed. Please check credentials."
}
```

**Server Error** (500):
```json
{
  "success": false,
  "message": "Setup failed: Connection timeout"
}
```

---

## 🧪 Testing

### Test Credentials:

**Sandbox Environment**:
```
Client ID: SU2512031928441979485878
Client Secret: 04652cf1-d98d-4f48-8ae8-0ecf60fac76f
Merchant ID: M22EOXLUSO1LA
Environment: sandbox
```

**Production Environment**:
```
Get from: Admin Panel → Payment Configuration → PhonePe
```

### Test UPI IDs:
```
success@ybl
failure@ybl
pending@ybl
```

### Test Flow:

1. **Setup Payment**:
```bash
curl -X POST https://your-domain.com/api/phonepe/autopay/setup \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": "test_user_123",
    "plan_id": "1",
    "amount": 1
  }'
```

2. **Check Status**:
```bash
curl https://your-domain.com/api/phonepe/autopay/status/MS_xyz789
```

3. **Trigger Payment**:
```bash
curl -X POST https://your-domain.com/api/phonepe/autopay/redeem \
  -H "Content-Type: application/json" \
  -d '{
    "merchant_subscription_id": "MS_xyz789"
  }'
```

---

## 📊 Database Tables

### Orders Table:
- Stores all payment orders
- Created automatically on payment initiation
- Updated via webhooks

### PhonePe Subscriptions Table:
- Stores AutoPay subscription details
- Tracks subscription status
- Links to orders

### PhonePe Transactions Table:
- Stores all PhonePe transactions
- Tracks payment history
- Used for reporting

### PhonePe Notifications Table:
- Stores all webhook notifications
- Audit trail for all events
- Debugging and monitoring

---

## 🔑 Configuration

### Admin Panel Setup:

1. Go to: **Admin Panel → Payment Configuration**
2. Select: **PhonePe**
3. Configure:
   ```
   Gateway: PhonePe
   Status: Active
   Environment: production (or sandbox)
   Client ID: [Your Client ID]
   Client Secret: [Your Client Secret]
   Merchant ID: [Your Merchant ID]
   ```

### Environment Variables:
```env
PHONEPE_MERCHANT_ID=M22EOXLUSO1LA
PHONEPE_CLIENT_ID=SU2512031928441979485878
PHONEPE_CLIENT_SECRET=04652cf1-d98d-4f48-8ae8-0ecf60fac76f
PHONEPE_ENVIRONMENT=production
```

---

## 📱 Frontend Integration Example

### React/React Native:

```javascript
// Setup AutoPay Subscription
const setupSubscription = async (userId, planId, amount) => {
  try {
    const response = await fetch('https://your-domain.com/api/phonepe/autopay/setup', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        user_id: userId,
        plan_id: planId,
        amount: amount
      })
    });
    
    const data = await response.json();
    
    if (data.success) {
      // Open redirect URL in browser/webview
      window.location.href = data.data.redirect_url;
      
      // Or for React Native:
      // Linking.openURL(data.data.redirect_url);
      
      // Save IDs for status checking
      localStorage.setItem('merchant_subscription_id', data.data.merchant_subscription_id);
      localStorage.setItem('merchant_order_id', data.data.merchant_order_id);
    } else {
      alert('Payment setup failed: ' + data.message);
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Network error occurred');
  }
};

// Check Subscription Status
const checkStatus = async (merchantSubscriptionId) => {
  try {
    const response = await fetch(
      `https://your-domain.com/api/phonepe/autopay/status/${merchantSubscriptionId}`
    );
    
    const data = await response.json();
    
    if (data.success) {
      console.log('Subscription Status:', data.data.phonepe_status);
      
      if (data.data.phonepe_status === 'ACTIVE') {
        alert('✅ Subscription is active!');
        // Navigate to success screen
      } else if (data.data.phonepe_status === 'PENDING') {
        alert('⏳ Waiting for mandate approval...');
        // Show pending screen
      }
    }
  } catch (error) {
    console.error('Error:', error);
  }
};

// One-Time Payment
const makePayment = async (planId, userDetails) => {
  try {
    const response = await fetch('https://your-domain.com/api/phonepe/payment', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        p: planId,
        name: userDetails.name,
        number: userDetails.mobile,
        email: userDetails.email,
        code: '' // promo code
      })
    });
    
    const data = await response.json();
    
    if (data.success && data.data.data.instrumentResponse) {
      const paymentUrl = data.data.data.instrumentResponse.redirectInfo.url;
      window.location.href = paymentUrl;
    }
  } catch (error) {
    console.error('Error:', error);
  }
};
```

---

## 🎯 Production Checklist

- [ ] Update credentials in Payment Configuration
- [ ] Set environment to `production`
- [ ] Configure webhook URL in PhonePe dashboard
- [ ] Test one-time payment
- [ ] Test AutoPay subscription
- [ ] Test webhook reception
- [ ] Verify order creation
- [ ] Test refund flow
- [ ] Monitor logs for errors
- [ ] Set up cron jobs for recurring payments

---

## 📞 Support

For issues or questions:
- Check logs: `storage/logs/laravel.log`
- Admin panel: `/phonepe/dashboard`
- Transactions: `/phonepe/transactions`
- Notifications: `/phonepe/notifications`

---

## 🔄 API Version

**Current Version**: v2  
**Last Updated**: February 2026  
**PhonePe API Version**: OAuth 2.0 / Subscriptions v2

---

**End of Documentation**
