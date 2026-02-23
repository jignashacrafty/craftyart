# PhonePe AutoPay QR Code - React Final Guide

## ✅ API Response

```json
{
  "statusCode": 200,
  "success": true,
  "msg": "QR Code generated successfully",
  "merchant_order_id": "MO_QR_...",
  "merchant_subscription_id": "MS_QR_...",
  "qr_code": {
    "url_for_qr": "https://mercury-t2.phonepe.com/transact/pgv3?token=...",
    "raw_upi_string": "https://mercury-t2.phonepe.com/transact/pgv3?token=...",
    "instructions": {
      "en": "Scan this QR code with any UPI app to set up AutoPay mandate",
      "hi": "AutoPay mandate सेट करने के लिए किसी भी UPI ऐप से इस QR कोड को स्कैन करें",
      "gu": "AutoPay mandate સેટ કરવા માટે કોઈપણ UPI એપ્લિકેશન વડે આ QR કોડ સ્કેન કરો"
    },
    "note": "Use this URL in React QR code library like qrcode.react or react-qr-code"
  }
}
```

## 🎨 React Implementation

### Step 1: Install QR Code Library

```bash
npm install qrcode.react
# OR
npm install react-qr-code
```

### Step 2: React Component (Using qrcode.react)

```jsx
import React, { useState } from 'react';
import QRCode from 'qrcode.react';

function PhonePeQRPayment() {
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);

  const generateQR = async () => {
    setLoading(true);
    
    try {
      const response = await fetch(
        'http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            user_id: 'test_user_123',
            plan_id: 'plan_monthly_99',
            amount: 1,
            upi: 'vrajsurani606@okaxis',
            target_app: 'com.phonepe.app'
          })
        }
      );

      const data = await response.json();
      
      if (data.success) {
        setQrData(data);
      } else {
        alert('Error: ' + data.msg);
      }
    } catch (error) {
      alert('Network error: ' + error.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="phonepe-qr-container">
      <h2>PhonePe AutoPay Payment</h2>

      {!qrData && (
        <button onClick={generateQR} disabled={loading}>
          {loading ? 'Generating QR Code...' : 'Generate QR Code'}
        </button>
      )}

      {qrData && (
        <div className="qr-display">
          <h3>Scan QR Code to Pay</h3>
          
          {/* QR Code using qrcode.react */}
          <QRCode 
            value={qrData.qr_code.url_for_qr}
            size={300}
            level="H"
            includeMargin={true}
            style={{
              border: '3px solid #5f259f',
              borderRadius: '8px',
              padding: '10px'
            }}
          />

          {/* Instructions in Gujarati */}
          <div className="instructions">
            <p>{qrData.qr_code.instructions.gu}</p>
          </div>

          {/* Step-by-step guide */}
          <div className="steps">
            <p>✅ {qrData.instructions.step_1}</p>
            <p>✅ {qrData.instructions.step_2}</p>
            <p>✅ {qrData.instructions.step_3}</p>
            <p>✅ {qrData.instructions.step_4}</p>
          </div>

          {/* Payment Details */}
          <div className="details">
            <p><strong>Order ID:</strong> {qrData.merchant_order_id}</p>
            <p><strong>Subscription ID:</strong> {qrData.merchant_subscription_id}</p>
            <p><strong>Status:</strong> {qrData.state}</p>
          </div>

          {/* Mobile: Direct Link */}
          <button 
            onClick={() => window.location.href = qrData.qr_code.url_for_qr}
            className="mobile-btn"
          >
            Open in PhonePe App
          </button>
        </div>
      )}
    </div>
  );
}

export default PhonePeQRPayment;
```

### Step 3: Alternative - Using react-qr-code

```jsx
import React, { useState } from 'react';
import { QRCodeSVG } from 'react-qr-code';

function PhonePeQRPayment() {
  const [qrData, setQrData] = useState(null);

  // ... same generateQR function ...

  return (
    <div>
      {qrData && (
        <div>
          <h3>Scan QR Code</h3>
          
          {/* QR Code using react-qr-code */}
          <QRCodeSVG 
            value={qrData.qr_code.url_for_qr}
            size={300}
            level="H"
            style={{
              border: '3px solid #5f259f',
              borderRadius: '8px',
              padding: '10px'
            }}
          />

          <p>{qrData.qr_code.instructions.gu}</p>
        </div>
      )}
    </div>
  );
}
```

## 🎨 CSS Styling

```css
.phonepe-qr-container {
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

.qr-display h3 {
  color: #5f259f;
  margin-bottom: 20px;
}

.instructions {
  margin: 20px 0;
  padding: 15px;
  background: white;
  border-radius: 8px;
  color: #333;
}

.steps {
  text-align: left;
  margin: 20px 0;
  padding: 15px;
  background: white;
  border-radius: 8px;
}

.steps p {
  margin: 10px 0;
  color: #555;
}

.details {
  text-align: left;
  margin: 20px 0;
  padding: 15px;
  background: white;
  border-radius: 8px;
}

.details p {
  margin: 8px 0;
  color: #333;
}

.mobile-btn {
  margin-top: 20px;
  padding: 15px 30px;
  background: #5f259f;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
}

.mobile-btn:hover {
  background: #4a1d7a;
  transform: translateY(-2px);
}

button:disabled {
  background: #ccc;
  cursor: not-allowed;
}
```

## 📱 Responsive Design

```css
@media (max-width: 768px) {
  .phonepe-qr-container {
    padding: 10px;
  }

  .qr-display {
    padding: 15px;
  }

  /* Hide QR code on mobile, show button instead */
  .qr-display canvas,
  .qr-display svg {
    display: none;
  }

  .mobile-btn {
    display: block;
    width: 100%;
  }
}

@media (min-width: 769px) {
  /* Hide mobile button on desktop */
  .mobile-btn {
    display: none;
  }
}
```

## 🔄 With Status Polling

```jsx
import React, { useState, useEffect } from 'react';
import QRCode from 'qrcode.react';

function PhonePeQRPaymentWithStatus() {
  const [qrData, setQrData] = useState(null);
  const [status, setStatus] = useState('PENDING');
  const [loading, setLoading] = useState(false);

  const generateQR = async () => {
    setLoading(true);
    
    const response = await fetch('/api/phonepe/autopay/generate-qr', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        user_id: 'user123',
        plan_id: 'plan',
        amount: 99
      })
    });

    const data = await response.json();
    
    if (data.success) {
      setQrData(data);
      startStatusPolling(data.merchant_subscription_id);
    }
    
    setLoading(false);
  };

  const checkStatus = async (subscriptionId) => {
    const response = await fetch(
      `/api/phonepe/autopay/status/${subscriptionId}`
    );
    const data = await response.json();
    
    if (data.success) {
      setStatus(data.data.phonepe_status);
      return data.data.phonepe_status;
    }
  };

  const startStatusPolling = (subscriptionId) => {
    const interval = setInterval(async () => {
      const currentStatus = await checkStatus(subscriptionId);
      
      if (currentStatus === 'ACTIVE') {
        clearInterval(interval);
        alert('✅ Payment Successful! Subscription is now ACTIVE');
      }
    }, 5000); // Check every 5 seconds

    // Stop after 5 minutes
    setTimeout(() => clearInterval(interval), 300000);
  };

  return (
    <div>
      {!qrData && (
        <button onClick={generateQR} disabled={loading}>
          {loading ? 'Generating...' : 'Generate QR Code'}
        </button>
      )}

      {qrData && (
        <div>
          <div className="status-badge">
            Status: {status}
          </div>

          <QRCode 
            value={qrData.qr_code.url_for_qr}
            size={300}
            level="H"
          />

          <p>{qrData.qr_code.instructions.gu}</p>

          {status === 'ACTIVE' && (
            <div className="success">
              ✅ Payment Successful!
            </div>
          )}
        </div>
      )}
    </div>
  );
}
```

## 📦 Package Comparison

| Package | Size | Features | Recommendation |
|---------|------|----------|----------------|
| `qrcode.react` | Small | Canvas-based, simple | ✅ Best for most cases |
| `react-qr-code` | Small | SVG-based, scalable | ✅ Good for responsive |
| `qrcode` | Medium | Node.js library | ❌ Overkill for React |

## 🎯 Complete Example

```jsx
import React, { useState } from 'react';
import QRCode from 'qrcode.react';
import './PhonePeQR.css';

function PhonePeQRPayment() {
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const generateQR = async () => {
    setLoading(true);
    setError(null);

    try {
      const response = await fetch(
        'http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr',
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            user_id: 'test_user_123',
            plan_id: 'plan_monthly_99',
            amount: 1,
            upi: 'vrajsurani606@okaxis'
          })
        }
      );

      const data = await response.json();

      if (data.success) {
        setQrData(data);
      } else {
        setError(data.msg || 'Failed to generate QR code');
      }
    } catch (err) {
      setError('Network error: ' + err.message);
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="phonepe-qr-container">
      <div className="header">
        <h2>PhonePe AutoPay Payment</h2>
        <p>Scan QR code to set up automatic payments</p>
      </div>

      {error && (
        <div className="error-box">
          ⚠️ {error}
        </div>
      )}

      {!qrData && (
        <button 
          onClick={generateQR} 
          disabled={loading}
          className="generate-btn"
        >
          {loading ? 'Generating QR Code...' : 'Generate QR Code'}
        </button>
      )}

      {qrData && (
        <div className="qr-display">
          <h3>Scan QR Code to Pay</h3>
          
          <div className="qr-wrapper">
            <QRCode 
              value={qrData.qr_code.url_for_qr}
              size={300}
              level="H"
              includeMargin={true}
            />
          </div>

          <div className="instructions">
            <p className="gujarati">{qrData.qr_code.instructions.gu}</p>
          </div>

          <div className="steps">
            <h4>How to Pay:</h4>
            <ol>
              <li>{qrData.instructions.step_1}</li>
              <li>{qrData.instructions.step_2}</li>
              <li>{qrData.instructions.step_3}</li>
              <li>{qrData.instructions.step_4}</li>
            </ol>
          </div>

          <div className="payment-info">
            <div className="info-row">
              <span>Order ID:</span>
              <code>{qrData.merchant_order_id}</code>
            </div>
            <div className="info-row">
              <span>Subscription ID:</span>
              <code>{qrData.merchant_subscription_id}</code>
            </div>
            <div className="info-row">
              <span>Status:</span>
              <span className="status">{qrData.state}</span>
            </div>
          </div>

          <button 
            onClick={() => window.location.href = qrData.qr_code.url_for_qr}
            className="mobile-btn"
          >
            Open in PhonePe App
          </button>
        </div>
      )}
    </div>
  );
}

export default PhonePeQRPayment;
```

## ✅ Summary

1. Install `qrcode.react` package
2. Use `url_for_qr` from API response
3. Display QR code with `<QRCode>` component
4. Add mobile button for direct app opening
5. Poll status for payment confirmation

**API ready છે! React માં QR code display કરી શકો છો! 🚀**
