# PhonePe AutoPay QR Code - React Implementation Guide (ગુજરાતી)

## 🎯 ઝાંખી

આ guide React developers માટે છે જે PhonePe AutoPay માટે QR code generate અને display કરવા માંગે છે. Image માં જે રીતે QR code દેખાય છે તે જ રીતે તમારા React app માં બતાવી શકશો.

## 📍 API Endpoint

```
POST {{base_url}}/api/phonepe/autopay/generate-qr
```

**⚠️ CSRF TOKEN ની જરૂર નથી** - આ API route છે!

## 🚀 Quick Start

### Step 1: API Call કરો

```javascript
const response = await fetch('http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    user_id: 'user123',           // જરૂરી
    plan_id: 'monthly_plan',      // જરૂરી
    amount: 99,                   // જરૂરી (minimum 1)
    upi: 'user@okaxis',          // વૈકલ્પિક
    target_app: 'com.phonepe.app' // વૈકલ્પિક
  })
});

const data = await response.json();
```

### Step 2: QR Code Display કરો

```jsx
<img 
  src={data.data.qr_code.base64} 
  alt="Scan to Pay" 
  style={{ width: '300px', height: '300px' }}
/>
```

### Step 3: Instructions બતાવો

```jsx
<div>
  <h3>QR Code Scan કરો</h3>
  <p>{data.data.instructions.step_1}</p>
  <p>{data.data.instructions.step_2}</p>
  <p>{data.data.instructions.step_3}</p>
  <p>{data.data.instructions.step_4}</p>
</div>
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
      "intent_url": "upi://pay?pa=merchant@upi&pn=MerchantName...",
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

## 🎨 સંપૂર્ણ React Component

```jsx
import React, { useState } from 'react';
import './PhonePeQR.css';

function PhonePeQRPayment() {
  const [qrData, setQrData] = useState(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);

  const generateQRCode = async () => {
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
            user_id: 'user123',
            plan_id: 'monthly_plan',
            amount: 99,
            upi: 'user@okaxis'
          })
        }
      );

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
    <div className="phonepe-qr-container">
      <div className="header">
        <img src="/phonepe-logo.png" alt="PhonePe" className="logo" />
        <h2>Scan QR Code to Pay</h2>
        <p>Open any UPI app on your phone and scan this QR code</p>
      </div>

      {!qrData && (
        <button 
          onClick={generateQRCode} 
          disabled={loading}
          className="generate-btn"
        >
          {loading ? 'Generating QR Code...' : 'Generate QR Code'}
        </button>
      )}

      {error && (
        <div className="error-box">
          <span className="error-icon">⚠️</span>
          {error}
        </div>
      )}

      {qrData && (
        <div className="qr-display-box">
          {/* QR Code */}
          <div className="qr-code-wrapper">
            <img 
              src={qrData.qr_code.base64} 
              alt="Scan to Pay" 
              className="qr-code-image"
            />
          </div>

          {/* How to Pay Instructions */}
          <div className="instructions-box">
            <div className="instruction-header">
              <span className="info-icon">ℹ️</span>
              <h3>How to Pay</h3>
            </div>
            <ol className="instruction-list">
              <li>
                <span className="step-number">1</span>
                <span>{qrData.instructions.step_1}</span>
              </li>
              <li>
                <span className="step-number">2</span>
                <span>{qrData.instructions.step_2}</span>
              </li>
              <li>
                <span className="step-number">3</span>
                <span>{qrData.instructions.step_3}</span>
              </li>
              <li>
                <span className="step-number">4</span>
                <span>{qrData.instructions.step_4}</span>
              </li>
            </ol>
          </div>

          {/* Supported UPI Apps */}
          <div className="supported-apps">
            <p>SUPPORTED UPI APPS</p>
            <div className="app-icons">
              <img src="/phonepe-icon.png" alt="PhonePe" />
              <img src="/gpay-icon.png" alt="Google Pay" />
              <img src="/paytm-icon.png" alt="Paytm" />
              <img src="/bhim-icon.png" alt="BHIM" />
            </div>
          </div>

          {/* Payment Details */}
          <div className="payment-details-box">
            <div className="detail-row">
              <span className="label">Amount:</span>
              <span className="value">₹{qrData.qr_code.decoded_params.am}</span>
            </div>
            <div className="detail-row">
              <span className="label">Merchant:</span>
              <span className="value">{qrData.qr_code.decoded_params.pn}</span>
            </div>
            <div className="detail-row">
              <span className="label">Order ID:</span>
              <span className="value order-id">{qrData.merchant_order_id}</span>
            </div>
          </div>

          {/* Waiting for Payment */}
          <div className="waiting-box">
            <div className="spinner"></div>
            <p>Waiting for payment...</p>
            <small>વપરાશકર્તા દ્વારા રકમ ચૂકવવામાં આવે તે રાહ જોઈ રહ્યા છીએ</small>
          </div>
        </div>
      )}
    </div>
  );
}

export default PhonePeQRPayment;
```

## 🎨 CSS Styling (PhonePeQR.css)

```css
.phonepe-qr-container {
  max-width: 500px;
  margin: 0 auto;
  padding: 20px;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

.header {
  text-align: center;
  color: white;
  margin-bottom: 30px;
}

.header .logo {
  width: 80px;
  height: 80px;
  margin-bottom: 15px;
}

.header h2 {
  font-size: 24px;
  margin: 10px 0;
}

.header p {
  font-size: 14px;
  opacity: 0.9;
}

.generate-btn {
  width: 100%;
  padding: 15px;
  background: #5f259f;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
}

.generate-btn:hover {
  background: #4a1d7a;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

.generate-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  transform: none;
}

.error-box {
  background: #ffebee;
  color: #c62828;
  padding: 15px;
  border-radius: 8px;
  margin: 15px 0;
  display: flex;
  align-items: center;
  gap: 10px;
}

.error-icon {
  font-size: 24px;
}

.qr-display-box {
  background: white;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.1);
}

.qr-code-wrapper {
  text-align: center;
  margin-bottom: 30px;
}

.qr-code-image {
  width: 300px;
  height: 300px;
  border: 3px solid #5f259f;
  border-radius: 12px;
  padding: 15px;
  background: white;
  box-shadow: 0 4px 16px rgba(95, 37, 159, 0.2);
}

.instructions-box {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.instruction-header {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 15px;
}

.info-icon {
  font-size: 24px;
  color: #5f259f;
}

.instruction-header h3 {
  margin: 0;
  color: #333;
  font-size: 18px;
}

.instruction-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.instruction-list li {
  display: flex;
  align-items: flex-start;
  gap: 15px;
  margin-bottom: 15px;
  color: #555;
  line-height: 1.6;
}

.step-number {
  background: #5f259f;
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  flex-shrink: 0;
}

.supported-apps {
  text-align: center;
  margin: 25px 0;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 12px;
}

.supported-apps p {
  font-size: 12px;
  color: #888;
  margin-bottom: 15px;
  font-weight: bold;
  letter-spacing: 1px;
}

.app-icons {
  display: flex;
  justify-content: center;
  gap: 20px;
}

.app-icons img {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.payment-details-box {
  background: #f5f5f5;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 20px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 12px 0;
  border-bottom: 1px solid #e0e0e0;
}

.detail-row:last-child {
  border-bottom: none;
}

.detail-row .label {
  color: #666;
  font-size: 14px;
}

.detail-row .value {
  color: #333;
  font-weight: bold;
  font-size: 16px;
}

.order-id {
  font-family: 'Courier New', monospace;
  font-size: 12px;
  background: #e0e0e0;
  padding: 4px 8px;
  border-radius: 4px;
}

.waiting-box {
  text-align: center;
  padding: 20px;
  background: #fff3cd;
  border-radius: 12px;
  border: 2px dashed #ffc107;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #5f259f;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 15px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.waiting-box p {
  margin: 10px 0 5px;
  color: #856404;
  font-weight: bold;
}

.waiting-box small {
  color: #856404;
  font-size: 12px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
  .phonepe-qr-container {
    padding: 10px;
  }

  .qr-code-image {
    width: 250px;
    height: 250px;
  }

  .qr-display-box {
    padding: 20px;
  }

  .app-icons img {
    width: 40px;
    height: 40px;
  }
}
```

## 🔄 Status Check સાથે Advanced Component

```jsx
import React, { useState, useEffect } from 'react';

function PhonePeQRWithStatus() {
  const [qrData, setQrData] = useState(null);
  const [status, setStatus] = useState('PENDING');
  const [loading, setLoading] = useState(false);

  // QR Code Generate કરો
  const generateQR = async () => {
    setLoading(true);
    
    const response = await fetch(
      'http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/generate-qr',
      {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          user_id: 'user123',
          plan_id: 'monthly_plan',
          amount: 99
        })
      }
    );

    const data = await response.json();
    
    if (data.success) {
      setQrData(data.data);
      startStatusCheck(data.data.merchant_subscription_id);
    }
    
    setLoading(false);
  };

  // Status Check કરો
  const checkStatus = async (subscriptionId) => {
    const response = await fetch(
      `http://localhost/git_jignasha/craftyart/public/api/phonepe/autopay/status/${subscriptionId}`,
      {
        method: 'GET',
        headers: { 'Accept': 'application/json' }
      }
    );

    const data = await response.json();
    
    if (data.success) {
      setStatus(data.data.phonepe_status);
      return data.data.phonepe_status;
    }
  };

  // દર 5 સેકંડે status check કરો
  const startStatusCheck = (subscriptionId) => {
    const interval = setInterval(async () => {
      const currentStatus = await checkStatus(subscriptionId);
      
      // જો ACTIVE થઈ ગયું હોય તો polling બંધ કરો
      if (currentStatus === 'ACTIVE') {
        clearInterval(interval);
        alert('✅ Payment Successful! Subscription is now ACTIVE');
      }
    }, 5000);

    // 5 મિનિટ પછી polling બંધ કરો
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

          <img src={qrData.qr_code.base64} alt="QR Code" />

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

## 📱 Mobile Deep Linking

જો તમે mobile પર છો અને direct UPI app open કરવા માંગો છો:

```jsx
// UPI Intent URL નો ઉપયોગ કરો
const openUPIApp = () => {
  window.location.href = qrData.qr_code.intent_url;
};

<button onClick={openUPIApp}>
  Open in PhonePe App
</button>
```

## 🎯 Key Points

✅ **No QR Library Needed** - Backend થી base64 image મળે છે  
✅ **Direct Use** - `<img>` tag માં સીધો use કરો  
✅ **Real-time Status** - Polling દ્વારા status check કરો  
✅ **Mobile Friendly** - Responsive design  
✅ **UPI Deep Link** - Direct app open કરવા માટે  

## 🧪 Testing Steps

1. Postman માં API test કરો
2. Response માં `qr_code.base64` copy કરો
3. React component માં paste કરો
4. Browser માં QR code દેખાશે
5. Mobile થી scan કરો અને payment કરો

## 📞 Support

કોઈ પણ મદદ માટે documentation check કરો અથવા contact કરો.
