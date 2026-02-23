# PhonePe AutoPay QR Code - React Implementation Guide

## 🎯 Overview

આ guide React developers માટે છે જે PhonePe AutoPay માટે QR code generate અને display કરવા માંગે છે.

## 📍 API Endpoint

```
POST {{base_url}}/api/phonepe/autopay/generate-qr
```

**⚠️ NO CSRF TOKEN REQUIRED** - આ API route છે!

## 📝 Request Format

```javascript
const response = await fetch('http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_id: 'user123',           // Required
    plan_id: 'monthly_plan',      // Required
    amount: 99,                   // Required (minimum 1)
    upi: 'user@okaxis',          // Optional
    target_app: 'com.phonepe.app' // Optional
  })
});

const data = await response.json();
```

## 📤 Response Format

```json
{
  "success": true,
  "message": "QR Code generated successfully",
  "data": {
    "merchant_order_id": "MO_ABC123...",
    "merchant_subscription_id": "MS_XYZ789...",
    "phonepe_order_id": "PHONEPE_ORDER_123",
    "state": "PENDING",
    "expire_at": "2026-02-21T12:00:00Z",
    "qr_code": {
      "base64": "data:image/png;base64,iVBORw0KGgoAAAANS...",
      "intent_url": "upi://pay?pa=merchant@upi&pn=MerchantName&am=99.00&cu=INR&tn=Payment",
      "decoded_params": {
        "pa": "merchant@upi",
        "pn": "Merchant Name",
        "am": "99.00",
        "cu": "INR",
        "tn": "Transaction note"
      }
    },
    "instructions": {
      "step_1": "Open any UPI app (PhonePe, GPay, Paytm, etc.)",
      "step_2": "Tap on 'Scan QR Code' option",
      "step_3": "Scan this QR code with your phone camera",
      "step_4": "Verify amount and complete payment"
    }
  }
}
```

## 🎨 React Component Example

### Basic Implementation

```jsx
import React, { useState } from 'react';

function PhonePeQRPayment() {
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const generateQRCode = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          user_id: 'user123',
          plan_id: 'monthly_plan',
          amount: 99,
          upi: 'user@okaxis'
        })
      });

      const data = await response.json();

      if (data.success) {
        setQrData(data.data);
      } else {
        setError(data.message || 'QR Code generation failed');
      }
    } catch (err) {
      setError('Network error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="qr-payment-container">
      <h2>PhonePe AutoPay Payment</h2>

      {!qrData && (
        <button onClick={generateQRCode} disabled={loading}>
          {loading ? 'Generating...' : 'Generate QR Code'}
        </button>
      )}

      {error && (
        <div className="error-message">
          {error}
        </div>
      )}

      {qrData && (
        <div className="qr-display">
          <h3>Scan QR Code to Pay</h3>
          
          {/* QR Code Image */}
          <img 
            src={qrData.qr_code.base64} 
            alt="Scan to Pay" 
            style={{ 
              width: '300px', 
              height: '300px',
              border: '2px solid #ccc',
              borderRadius: '8px',
              padding: '10px'
            }}
          />

          {/* Instructions */}
          <div className="instructions">
            <p>✅ {qrData.instructions.step_1}</p>
            <p>✅ {qrData.instructions.step_2}</p>
            <p>✅ {qrData.instructions.step_3}</p>
            <p>✅ {qrData.instructions.step_4}</p>
          </div>

          {/* Payment Details */}
          <div className="payment-details">
            <p><strong>Amount:</strong> ₹{qrData.qr_code.decoded_params.am}</p>
            <p><strong>Merchant:</strong> {qrData.qr_code.decoded_params.pn}</p>
            <p><strong>Order ID:</strong> {qrData.merchant_order_id}</p>
          </div>

          {/* Subscription ID (Save this for status check) */}
          <input 
            type="hidden" 
            value={qrData.merchant_subscription_id} 
            id="subscription-id"
          />
        </div>
      )}
    </div>
  );
}

export default PhonePeQRPayment;
```

### Advanced Implementation with Status Check

```jsx
import React, { useState, useEffect } from 'react';

function PhonePeQRPaymentAdvanced() {
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [subscriptionStatus, setSubscriptionStatus] = useState(null);

  // Generate QR Code
  const generateQRCode = async (userId, planId, amount, upi) => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch('http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          user_id: userId,
          plan_id: planId,
          amount: amount,
          upi: upi
        })
      });

      const data = await response.json();

      if (data.success) {
        setQrData(data.data);
        // Start polling for status
        startStatusPolling(data.data.merchant_subscription_id);
      } else {
        setError(data.message || 'QR Code generation failed');
      }
    } catch (err) {
      setError('Network error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  // Check Subscription Status
  const checkStatus = async (subscriptionId) => {
    try {
      const response = await fetch(
        `http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/status/${subscriptionId}`,
        {
          method: 'GET',
          headers: {
            'Accept': 'application/json'
          }
        }
      );

      const data = await response.json();

      if (data.success) {
        setSubscriptionStatus(data.data.phonepe_status);
        
        // If status is ACTIVE, stop polling
        if (data.data.phonepe_status === 'ACTIVE') {
          return true; // Stop polling
        }
      }
    } catch (err) {
      console.error('Status check failed:', err);
    }
    
    return false; // Continue polling
  };

  // Poll status every 5 seconds
  const startStatusPolling = (subscriptionId) => {
    const interval = setInterval(async () => {
      const shouldStop = await checkStatus(subscriptionId);
      
      if (shouldStop) {
        clearInterval(interval);
        // Show success message
        alert('Payment successful! Subscription is now ACTIVE');
      }
    }, 5000); // Check every 5 seconds

    // Stop polling after 5 minutes
    setTimeout(() => {
      clearInterval(interval);
    }, 300000);
  };

  return (
    <div className="qr-payment-advanced">
      <h2>PhonePe AutoPay Payment</h2>

      {/* Payment Form */}
      {!qrData && (
        <form onSubmit={(e) => {
          e.preventDefault();
          const formData = new FormData(e.target);
          generateQRCode(
            formData.get('user_id'),
            formData.get('plan_id'),
            formData.get('amount'),
            formData.get('upi')
          );
        }}>
          <input name="user_id" placeholder="User ID" required />
          <input name="plan_id" placeholder="Plan ID" required />
          <input name="amount" type="number" placeholder="Amount" required />
          <input name="upi" placeholder="UPI ID (optional)" />
          <button type="submit" disabled={loading}>
            {loading ? 'Generating...' : 'Generate QR Code'}
          </button>
        </form>
      )}

      {error && <div className="error">{error}</div>}

      {/* QR Code Display */}
      {qrData && (
        <div className="qr-display">
          <div className="qr-header">
            <h3>Scan QR Code to Pay</h3>
            {subscriptionStatus && (
              <span className={`status status-${subscriptionStatus.toLowerCase()}`}>
                Status: {subscriptionStatus}
              </span>
            )}
          </div>

          <img 
            src={qrData.qr_code.base64} 
            alt="Scan to Pay"
            className="qr-image"
          />

          <div className="instructions">
            {Object.values(qrData.instructions).map((instruction, index) => (
              <p key={index}>✅ {instruction}</p>
            ))}
          </div>

          <div className="payment-info">
            <div className="info-row">
              <span>Amount:</span>
              <strong>₹{qrData.qr_code.decoded_params.am}</strong>
            </div>
            <div className="info-row">
              <span>Merchant:</span>
              <strong>{qrData.qr_code.decoded_params.pn}</strong>
            </div>
            <div className="info-row">
              <span>Order ID:</span>
              <code>{qrData.merchant_order_id}</code>
            </div>
          </div>

          {subscriptionStatus === 'ACTIVE' && (
            <div className="success-message">
              ✅ Payment Successful! Your subscription is now active.
            </div>
          )}
        </div>
      )}
    </div>
  );
}

export default PhonePeQRPaymentAdvanced;
```

## 🎨 CSS Styling

```css
.qr-payment-container {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  font-family: Arial, sans-serif;
}

.qr-display {
  text-align: center;
  background: #f9f9f9;
  padding: 30px;
  border-radius: 12px;
  box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.qr-image {
  width: 300px;
  height: 300px;
  border: 2px solid #5f259f;
  border-radius: 8px;
  padding: 10px;
  background: white;
  margin: 20px 0;
}

.instructions {
  text-align: left;
  margin: 20px 0;
  padding: 15px;
  background: white;
  border-radius: 8px;
}

.instructions p {
  margin: 10px 0;
  color: #333;
}

.payment-details {
  text-align: left;
  margin-top: 20px;
  padding: 15px;
  background: white;
  border-radius: 8px;
}

.error-message {
  color: #d32f2f;
  background: #ffebee;
  padding: 15px;
  border-radius: 8px;
  margin: 15px 0;
}

.status {
  display: inline-block;
  padding: 5px 15px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: bold;
  margin-left: 10px;
}

.status-pending {
  background: #fff3cd;
  color: #856404;
}

.status-active {
  background: #d4edda;
  color: #155724;
}

.success-message {
  background: #d4edda;
  color: #155724;
  padding: 15px;
  border-radius: 8px;
  margin-top: 20px;
  font-weight: bold;
}
```

## 📱 Mobile Responsive Design

```css
@media (max-width: 768px) {
  .qr-payment-container {
    padding: 10px;
  }

  .qr-image {
    width: 250px;
    height: 250px;
  }

  .qr-display {
    padding: 15px;
  }
}
```

## 🔄 Status Polling Logic

```javascript
// Utility function for status polling
const pollSubscriptionStatus = async (subscriptionId, onStatusChange, maxAttempts = 60) => {
  let attempts = 0;
  
  const poll = async () => {
    if (attempts >= maxAttempts) {
      console.log('Max polling attempts reached');
      return;
    }

    try {
      const response = await fetch(
        `http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/status/${subscriptionId}`,
        {
          method: 'GET',
          headers: { 'Accept': 'application/json' }
        }
      );

      const data = await response.json();

      if (data.success) {
        onStatusChange(data.data.phonepe_status);

        // Stop polling if status is final
        if (['ACTIVE', 'COMPLETED', 'FAILED', 'CANCELLED'].includes(data.data.phonepe_status)) {
          return;
        }
      }

      attempts++;
      setTimeout(poll, 5000); // Poll every 5 seconds
    } catch (err) {
      console.error('Polling error:', err);
      attempts++;
      setTimeout(poll, 5000);
    }
  };

  poll();
};

// Usage
pollSubscriptionStatus('MS_ABC123...', (status) => {
  console.log('Status updated:', status);
  setSubscriptionStatus(status);
});
```

## 🎯 Key Features

✅ **No External QR Library Needed** - QR code આવે છે base64 format માં  
✅ **Ready to Use** - Direct `<img>` tag માં use કરી શકો  
✅ **UPI Intent URL** - Deep linking માટે  
✅ **Decoded Parameters** - Custom UI બનાવવા માટે  
✅ **Step-by-step Instructions** - User guidance માટે  
✅ **Status Polling** - Real-time payment status  

## 🔍 Testing

```bash
# Postman માં test કરો
POST http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr

Body:
{
  "user_id": "test_user_123",
  "plan_id": "plan_monthly_99",
  "amount": 1,
  "upi": "vrajsurani606@okaxis"
}
```

## 📞 Support

કોઈ પણ issue માટે contact કરો અથવા documentation check કરો.
