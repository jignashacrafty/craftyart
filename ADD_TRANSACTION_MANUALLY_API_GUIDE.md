# Add Transaction Manually API - Complete Guide

## API Endpoint
```
POST {{api_base_url}}/order-user/add-transaction-manually
```

## Authentication
⚠️ **REQUIRED**: Bearer Token Authentication

```
Authorization: Bearer YOUR_JWT_TOKEN
```

## Request Body

### Required Fields

| Field | Type | Description | Example |
|-------|------|-------------|---------|
| `email` | string (email) | User email address (must exist) | `"user@example.com"` |
| `contact` | string (max 15) | User contact number | `"9876543210"` |
| `method` | string | Payment method | `"Razorpay"`, `"PhonePe"`, `"Bank Transfer"` |
| `transaction_id` | string | Unique transaction ID | `"TXN123456789"` |
| `currency_code` | string | Currency code | `"INR"` or `"USD"` |
| `price_amount` | number | Original plan price | `999` |
| `paid_amount` | number | Amount actually paid | `999` |
| `plan_id` | integer | Subscription plan ID | `1` |
| `usage_purpose` | string | Usage type | `"personal"` or `"professional"` |

### Optional Fields

| Field | Type | Description | Default | Example |
|-------|------|-------------|---------|---------|
| `fromWallet` | boolean | Payment from wallet | `0` | `0` or `1` |
| `fromWhere` | string | Source of transaction | `"Manual"` | `"Mobile"`, `"Web"`, `"Manual"` |
| `coins` | integer | Coins to add | `0` | `100` |

## Request Example (JSON)

```json
{
  "email": "vrajsurani606@gmail.com",
  "contact": "9876543210",
  "method": "Razorpay",
  "transaction_id": "TXN_1708689600",
  "currency_code": "INR",
  "price_amount": 999,
  "paid_amount": 999,
  "plan_id": 1,
  "usage_purpose": "professional",
  "fromWallet": 0,
  "fromWhere": "Mobile",
  "coins": 0
}
```

## Response Examples

### Success Response (200)

```json
{
  "success": true,
  "message": "Transaction added successfully",
  "data": {
    "transaction_log_id": 123,
    "user_id": 456,
    "transaction_id": "TXN_1708689600",
    "amount": 999,
    "currency": "INR",
    "plan_name": "Professional Plan",
    "expiry_date": "2026-03-23 15:30:00"
  }
}
```

### Error Responses

#### User Not Found (404)
```json
{
  "success": false,
  "message": "User not found with this email"
}
```

#### User Inactive (400)
```json
{
  "success": false,
  "message": "User account is inactive"
}
```

#### Plan Not Found (404)
```json
{
  "success": false,
  "message": "Plan not found"
}
```

#### Duplicate Transaction (400)
```json
{
  "success": false,
  "message": "Transaction ID already exists"
}
```

#### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."],
    "plan_id": ["The plan id must be an integer."]
  }
}
```

#### Server Error (500)
```json
{
  "success": false,
  "message": "Error adding transaction: Database connection failed"
}
```

## What This API Does

1. ✅ **Validates User**: Checks if user exists and is active
2. ✅ **Validates Plan**: Verifies plan exists in database
3. ✅ **Checks Uniqueness**: Ensures transaction ID is unique
4. ✅ **Creates Transaction**: Adds transaction log entry
5. ✅ **Deactivates Old Subscriptions**: Sets old subscriptions to inactive
6. ✅ **Calculates Expiry**: Sets expiry date based on plan validity
7. ✅ **Updates User Status**: Makes user premium
8. ✅ **Updates Usage**: Sets personal/professional usage purpose

## React/React Native Implementation

### Using Axios

```javascript
import axios from 'axios';

const addTransactionManually = async (transactionData) => {
  try {
    const token = localStorage.getItem('auth_token'); // or AsyncStorage for React Native
    
    const response = await axios.post(
      'http://your-domain.com/api/order-user/add-transaction-manually',
      {
        email: transactionData.email,
        contact: transactionData.contact,
        method: transactionData.method,
        transaction_id: `TXN_${Date.now()}`, // Auto-generate unique ID
        currency_code: transactionData.currency_code,
        price_amount: transactionData.price_amount,
        paid_amount: transactionData.paid_amount,
        plan_id: transactionData.plan_id,
        usage_purpose: transactionData.usage_purpose,
        fromWallet: transactionData.fromWallet || 0,
        fromWhere: 'Mobile', // or 'Web'
        coins: transactionData.coins || 0
      },
      {
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        }
      }
    );

    if (response.data.success) {
      console.log('✅ Transaction added successfully');
      console.log('Transaction ID:', response.data.data.transaction_log_id);
      console.log('Expiry Date:', response.data.data.expiry_date);
      return response.data;
    } else {
      console.error('❌ Transaction failed:', response.data.message);
      throw new Error(response.data.message);
    }
  } catch (error) {
    console.error('❌ API Error:', error.response?.data || error.message);
    throw error;
  }
};

// Usage Example
const handleAddTransaction = async () => {
  try {
    const result = await addTransactionManually({
      email: 'user@example.com',
      contact: '9876543210',
      method: 'Razorpay',
      currency_code: 'INR',
      price_amount: 999,
      paid_amount: 999,
      plan_id: 1,
      usage_purpose: 'professional',
      fromWallet: 0,
      coins: 0
    });
    
    alert(`Transaction successful! Expires on: ${result.data.expiry_date}`);
  } catch (error) {
    alert(`Transaction failed: ${error.message}`);
  }
};
```

### Using Fetch API

```javascript
const addTransactionManually = async (transactionData) => {
  try {
    const token = localStorage.getItem('auth_token');
    
    const response = await fetch(
      'http://your-domain.com/api/order-user/add-transaction-manually',
      {
        method: 'POST',
        headers: {
          'Authorization': `Bearer ${token}`,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          email: transactionData.email,
          contact: transactionData.contact,
          method: transactionData.method,
          transaction_id: `TXN_${Date.now()}`,
          currency_code: transactionData.currency_code,
          price_amount: transactionData.price_amount,
          paid_amount: transactionData.paid_amount,
          plan_id: transactionData.plan_id,
          usage_purpose: transactionData.usage_purpose,
          fromWallet: transactionData.fromWallet || 0,
          fromWhere: 'Mobile',
          coins: transactionData.coins || 0
        })
      }
    );

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || 'Transaction failed');
    }

    if (data.success) {
      console.log('✅ Transaction added successfully');
      return data;
    } else {
      throw new Error(data.message);
    }
  } catch (error) {
    console.error('❌ API Error:', error);
    throw error;
  }
};
```

### React Component Example

```jsx
import React, { useState } from 'react';
import axios from 'axios';

const AddTransactionForm = () => {
  const [formData, setFormData] = useState({
    email: '',
    contact: '',
    method: 'Razorpay',
    currency_code: 'INR',
    price_amount: 999,
    paid_amount: 999,
    plan_id: 1,
    usage_purpose: 'professional',
    fromWallet: 0,
    coins: 0
  });
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState(null);
  const [error, setError] = useState(null);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    setError(null);
    setResult(null);

    try {
      const token = localStorage.getItem('auth_token');
      
      const response = await axios.post(
        'http://your-domain.com/api/order-user/add-transaction-manually',
        {
          ...formData,
          transaction_id: `TXN_${Date.now()}`,
          fromWhere: 'Web'
        },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        setResult(response.data.data);
        alert('✅ Transaction added successfully!');
      }
    } catch (err) {
      setError(err.response?.data?.message || err.message);
      alert('❌ Transaction failed: ' + (err.response?.data?.message || err.message));
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="add-transaction-form">
      <h2>Add Transaction Manually</h2>
      
      <form onSubmit={handleSubmit}>
        <div>
          <label>Email:</label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            required
          />
        </div>

        <div>
          <label>Contact:</label>
          <input
            type="text"
            value={formData.contact}
            onChange={(e) => setFormData({...formData, contact: e.target.value})}
            required
          />
        </div>

        <div>
          <label>Payment Method:</label>
          <select
            value={formData.method}
            onChange={(e) => setFormData({...formData, method: e.target.value})}
          >
            <option value="Razorpay">Razorpay</option>
            <option value="PhonePe">PhonePe</option>
            <option value="Bank Transfer">Bank Transfer</option>
          </select>
        </div>

        <div>
          <label>Currency:</label>
          <select
            value={formData.currency_code}
            onChange={(e) => setFormData({...formData, currency_code: e.target.value})}
          >
            <option value="INR">INR</option>
            <option value="USD">USD</option>
          </select>
        </div>

        <div>
          <label>Amount:</label>
          <input
            type="number"
            value={formData.paid_amount}
            onChange={(e) => setFormData({...formData, paid_amount: parseFloat(e.target.value)})}
            required
          />
        </div>

        <div>
          <label>Plan ID:</label>
          <input
            type="number"
            value={formData.plan_id}
            onChange={(e) => setFormData({...formData, plan_id: parseInt(e.target.value)})}
            required
          />
        </div>

        <div>
          <label>Usage Purpose:</label>
          <select
            value={formData.usage_purpose}
            onChange={(e) => setFormData({...formData, usage_purpose: e.target.value})}
          >
            <option value="personal">Personal</option>
            <option value="professional">Professional</option>
          </select>
        </div>

        <button type="submit" disabled={loading}>
          {loading ? 'Processing...' : 'Add Transaction'}
        </button>
      </form>

      {result && (
        <div className="success-message">
          <h3>✅ Transaction Added Successfully!</h3>
          <p>Transaction ID: {result.transaction_log_id}</p>
          <p>User ID: {result.user_id}</p>
          <p>Amount: {result.amount} {result.currency}</p>
          <p>Plan: {result.plan_name}</p>
          <p>Expires: {result.expiry_date}</p>
        </div>
      )}

      {error && (
        <div className="error-message">
          <h3>❌ Error</h3>
          <p>{error}</p>
        </div>
      )}
    </div>
  );
};

export default AddTransactionForm;
```

## React Native Example

```javascript
import React, { useState } from 'react';
import { View, Text, TextInput, Button, Alert, ActivityIndicator } from 'react-native';
import AsyncStorage from '@react-native-async-storage/async-storage';
import axios from 'axios';

const AddTransactionScreen = () => {
  const [email, setEmail] = useState('');
  const [contact, setContact] = useState('');
  const [amount, setAmount] = useState('999');
  const [loading, setLoading] = useState(false);

  const handleAddTransaction = async () => {
    if (!email || !contact || !amount) {
      Alert.alert('Error', 'Please fill all required fields');
      return;
    }

    setLoading(true);

    try {
      const token = await AsyncStorage.getItem('auth_token');
      
      const response = await axios.post(
        'http://your-domain.com/api/order-user/add-transaction-manually',
        {
          email: email,
          contact: contact,
          method: 'Razorpay',
          transaction_id: `TXN_${Date.now()}`,
          currency_code: 'INR',
          price_amount: parseFloat(amount),
          paid_amount: parseFloat(amount),
          plan_id: 1,
          usage_purpose: 'professional',
          fromWallet: 0,
          fromWhere: 'Mobile',
          coins: 0
        },
        {
          headers: {
            'Authorization': `Bearer ${token}`,
            'Content-Type': 'application/json'
          }
        }
      );

      if (response.data.success) {
        Alert.alert(
          'Success',
          `Transaction added successfully!\nExpires: ${response.data.data.expiry_date}`,
          [{ text: 'OK' }]
        );
        
        // Clear form
        setEmail('');
        setContact('');
        setAmount('999');
      }
    } catch (error) {
      Alert.alert(
        'Error',
        error.response?.data?.message || error.message
      );
    } finally {
      setLoading(false);
    }
  };

  return (
    <View style={{ padding: 20 }}>
      <Text style={{ fontSize: 20, fontWeight: 'bold', marginBottom: 20 }}>
        Add Transaction Manually
      </Text>

      <TextInput
        placeholder="Email"
        value={email}
        onChangeText={setEmail}
        keyboardType="email-address"
        style={{ borderWidth: 1, padding: 10, marginBottom: 10 }}
      />

      <TextInput
        placeholder="Contact"
        value={contact}
        onChangeText={setContact}
        keyboardType="phone-pad"
        style={{ borderWidth: 1, padding: 10, marginBottom: 10 }}
      />

      <TextInput
        placeholder="Amount"
        value={amount}
        onChangeText={setAmount}
        keyboardType="numeric"
        style={{ borderWidth: 1, padding: 10, marginBottom: 20 }}
      />

      {loading ? (
        <ActivityIndicator size="large" color="#5f259f" />
      ) : (
        <Button
          title="Add Transaction"
          onPress={handleAddTransaction}
          color="#5f259f"
        />
      )}
    </View>
  );
};

export default AddTransactionScreen;
```

## Important Notes

### Transaction ID Generation
- Always use unique transaction IDs
- Recommended format: `TXN_${Date.now()}` or `TXN_${timestamp}`
- System checks for duplicate transaction IDs

### Currency Handling
- API now stores exact currency code: `INR` or `USD`
- Price is automatically selected based on currency:
  - INR → `plan.price`
  - USD → `plan.price_dollar`

### User Status
- User must exist in database
- User must be active (`status != 0`)
- User becomes premium immediately after transaction

### Subscription Management
- Old subscriptions are automatically deactivated
- New expiry date = Current date + Plan validity days
- Only one active subscription per user

### Error Handling
Always handle these scenarios:
- Network errors
- Authentication failures (401)
- Validation errors (422)
- User not found (404)
- Duplicate transaction (400)
- Server errors (500)

## Testing with Postman

1. **Get Auth Token**: Use Login API first
2. **Set Token**: Token is auto-saved in collection variable
3. **Update Request**: Modify email, contact, amount as needed
4. **Send Request**: Click Send button
5. **Check Response**: Verify success and data fields

## Postman Collection

The complete Postman collection is available in:
```
ORDER_USER_API_POSTMAN_COLLECTION_COMPLETE.json
```

Import this file in Postman to test all APIs.

---

**Status**: API Ready for React/React Native ✅  
**Authentication**: Bearer Token Required ✅  
**Postman Collection**: Updated ✅
