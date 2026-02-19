# Order User Management API - React Developer Guide

## 🎯 Overview

Welcome! This document contains everything you need to integrate the Order User Management system into your React application.

## 📦 What You're Getting

### API Endpoints: 11 Complete APIs
All functionality from `http://localhost/git_jignasha/craftyart/public/order_user` is now available as REST APIs.

### Documentation Files:
1. **ORDER_USER_API_DOCUMENTATION.md** - Complete API reference (English)
2. **ORDER_USER_API_QUICK_REFERENCE_GUJARATI.md** - Quick guide (Gujarati)
3. **ORDER_USER_API_POSTMAN_COLLECTION.json** - Postman collection for testing
4. **ORDER_USER_API_SUMMARY.md** - Implementation summary
5. **README_FOR_REACT_DEVELOPER.md** - This file

---

## 🚀 Quick Start (5 Minutes)

### Step 1: Test the APIs (2 minutes)

**Option A: Using Browser**
```
Open: http://localhost/git_jignasha/craftyart/public/api/order-user/followup-labels
```

**Option B: Using Command Line**
```bash
php test_order_user_api.php
```

**Option C: Using curl**
```bash
curl http://localhost/git_jignasha/craftyart/public/api/order-user/followup-labels
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "interested": "Interested",
    "highly_interested": "Highly Interested",
    ...
  }
}
```

### Step 2: Import Postman Collection (2 minutes)

1. Open Postman
2. Click Import
3. Select `ORDER_USER_API_POSTMAN_COLLECTION.json`
4. Set variables:
   - `base_url`: `http://localhost/git_jignasha/craftyart/public/api`
   - `token`: Your auth token (get from login API)
5. Test all endpoints

### Step 3: Read Documentation (1 minute)

- **English**: Open `ORDER_USER_API_DOCUMENTATION.md`
- **Gujarati**: Open `ORDER_USER_API_QUICK_REFERENCE_GUJARATI.md`

---

## 📋 Complete API List

| # | Endpoint | Method | Auth | Purpose |
|---|----------|--------|------|---------|
| 1 | `/order-user` | GET | Optional | Get all orders with filters |
| 2 | `/order-user/followup-update` | POST | Required | Update followup status |
| 3 | `/order-user/get-user-usage` | GET | Optional | Get user usage type |
| 4 | `/order-user/get-plans` | GET | Optional | Get subscription plans |
| 5 | `/order-user/validate-email` | POST | Optional | Validate email |
| 6 | `/order-user/create-payment-link` | POST | Optional | Create payment link |
| 7 | `/order-user/purchase-history/{userId}` | GET | Optional | Get purchase history |
| 8 | `/order-user/check-phonepe-status/{id}` | GET | Optional | Check PhonePe status |
| 9 | `/order-user/check-razorpay-status/{id}` | GET | Optional | Check Razorpay status |
| 10 | `/order-user/followup-labels` | GET | No | Get followup labels |
| 11 | `/order-user/new-orders` | GET | Optional | Get new orders |

---

## 💻 React Implementation

### 1. Create API Service

```javascript
// src/services/api/orderUserApi.js
const API_BASE = process.env.REACT_APP_API_BASE_URL || 
                 'http://localhost/git_jignasha/craftyart/public/api';

const getHeaders = (includeAuth = true) => {
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  };
  
  if (includeAuth) {
    const token = localStorage.getItem('auth_token');
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
  }
  
  return headers;
};

const handleResponse = async (response) => {
  const data = await response.json();
  
  if (!response.ok) {
    throw new Error(data.message || 'API request failed');
  }
  
  return data;
};

export const orderUserApi = {
  // Get orders with filters
  getOrders: async (filters = {}) => {
    const params = new URLSearchParams(filters);
    const response = await fetch(`${API_BASE}/order-user?${params}`, {
      headers: getHeaders()
    });
    return handleResponse(response);
  },

  // Update followup
  updateFollowup: async (data) => {
    const response = await fetch(`${API_BASE}/order-user/followup-update`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return handleResponse(response);
  },

  // Get user usage
  getUserUsage: async (orderId) => {
    const response = await fetch(
      `${API_BASE}/order-user/get-user-usage?order_id=${orderId}`,
      { headers: getHeaders(false) }
    );
    return handleResponse(response);
  },

  // Get plans
  getPlans: async (subscriptionType = 'new_sub') => {
    const response = await fetch(
      `${API_BASE}/order-user/get-plans?subscription_type=${subscriptionType}`,
      { headers: getHeaders(false) }
    );
    return handleResponse(response);
  },

  // Validate email
  validateEmail: async (email) => {
    const response = await fetch(`${API_BASE}/order-user/validate-email`, {
      method: 'POST',
      headers: getHeaders(false),
      body: JSON.stringify({ email })
    });
    return handleResponse(response);
  },

  // Create payment link
  createPaymentLink: async (data) => {
    const response = await fetch(`${API_BASE}/order-user/create-payment-link`, {
      method: 'POST',
      headers: getHeaders(),
      body: JSON.stringify(data)
    });
    return handleResponse(response);
  },

  // Get purchase history
  getPurchaseHistory: async (userId) => {
    const response = await fetch(
      `${API_BASE}/order-user/purchase-history/${userId}`,
      { headers: getHeaders(false) }
    );
    return handleResponse(response);
  },

  // Check PhonePe status
  checkPhonePeStatus: async (merchantOrderId) => {
    const response = await fetch(
      `${API_BASE}/order-user/check-phonepe-status/${merchantOrderId}`,
      { headers: getHeaders(false) }
    );
    return handleResponse(response);
  },

  // Check Razorpay status
  checkRazorpayStatus: async (paymentLinkId) => {
    const response = await fetch(
      `${API_BASE}/order-user/check-razorpay-status/${paymentLinkId}`,
      { headers: getHeaders(false) }
    );
    return handleResponse(response);
  },

  // Get followup labels
  getFollowupLabels: async () => {
    const response = await fetch(`${API_BASE}/order-user/followup-labels`, {
      headers: getHeaders(false)
    });
    return handleResponse(response);
  },

  // Get new orders
  getNewOrders: async (lastId = 0) => {
    const response = await fetch(
      `${API_BASE}/order-user/new-orders?last_id=${lastId}`,
      { headers: getHeaders() }
    );
    return handleResponse(response);
  }
};
```

### 2. Create Custom Hook

```javascript
// src/hooks/useOrderUser.js
import { useState, useEffect, useCallback } from 'react';
import { orderUserApi } from '../services/api/orderUserApi';

export const useOrderUser = (initialFilters = {}) => {
  const [orders, setOrders] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [pagination, setPagination] = useState(null);
  const [filters, setFilters] = useState({
    per_page: 15,
    page: 1,
    ...initialFilters
  });

  const loadOrders = useCallback(async () => {
    setLoading(true);
    setError(null);
    
    try {
      const result = await orderUserApi.getOrders(filters);
      
      if (result.success) {
        setOrders(result.data);
        setPagination(result.pagination);
      } else {
        setError(result.message);
      }
    } catch (err) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  }, [filters]);

  useEffect(() => {
    loadOrders();
  }, [loadOrders]);

  const updateFollowup = async (orderId, followupData) => {
    try {
      const result = await orderUserApi.updateFollowup({
        id: orderId,
        ...followupData
      });
      
      if (result.success) {
        // Refresh orders
        await loadOrders();
        return { success: true };
      } else {
        return { success: false, message: result.message };
      }
    } catch (err) {
      return { success: false, message: err.message };
    }
  };

  const changePage = (newPage) => {
    setFilters(prev => ({ ...prev, page: newPage }));
  };

  const updateFilters = (newFilters) => {
    setFilters(prev => ({ ...prev, ...newFilters, page: 1 }));
  };

  return {
    orders,
    loading,
    error,
    pagination,
    filters,
    updateFollowup,
    changePage,
    updateFilters,
    refresh: loadOrders
  };
};
```

### 3. Create Order List Component

```javascript
// src/components/OrderList.jsx
import React from 'react';
import { useOrderUser } from '../hooks/useOrderUser';

const OrderList = () => {
  const {
    orders,
    loading,
    error,
    pagination,
    updateFollowup,
    changePage,
    updateFilters
  } = useOrderUser();

  const handleFollowupUpdate = async (orderId) => {
    const result = await updateFollowup(orderId, {
      followup_call: 1,
      followup_note: 'Called customer',
      followup_label: 'interested'
    });
    
    if (result.success) {
      alert('Followup updated successfully');
    } else {
      alert(result.message);
    }
  };

  if (loading) return <div>Loading...</div>;
  if (error) return <div>Error: {error}</div>;

  return (
    <div className="order-list">
      {/* Filters */}
      <div className="filters">
        <select onChange={(e) => updateFilters({ status_filter: e.target.value })}>
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
        </select>
        
        <select onChange={(e) => updateFilters({ type_filter: e.target.value })}>
          <option value="">All Types</option>
          <option value="new_sub">New Subscription</option>
          <option value="old_sub">Old Subscription</option>
          <option value="template">Template</option>
        </select>
      </div>

      {/* Orders Table */}
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          {orders.map(order => (
            <tr key={order.id}>
              <td>{order.id}</td>
              <td>{order.user_name}</td>
              <td>{order.email}</td>
              <td>{order.contact_no}</td>
              <td>{order.amount_with_symbol}</td>
              <td>{order.status}</td>
              <td>
                <button onClick={() => handleFollowupUpdate(order.id)}>
                  Mark as Called
                </button>
              </td>
            </tr>
          ))}
        </tbody>
      </table>

      {/* Pagination */}
      {pagination && (
        <div className="pagination">
          <button 
            disabled={pagination.current_page === 1}
            onClick={() => changePage(pagination.current_page - 1)}
          >
            Previous
          </button>
          
          <span>
            Page {pagination.current_page} of {pagination.last_page}
          </span>
          
          <button 
            disabled={pagination.current_page === pagination.last_page}
            onClick={() => changePage(pagination.current_page + 1)}
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};

export default OrderList;
```

### 4. Create Payment Link Component

```javascript
// src/components/CreatePaymentLink.jsx
import React, { useState, useEffect } from 'react';
import { orderUserApi } from '../services/api/orderUserApi';

const CreatePaymentLink = () => {
  const [formData, setFormData] = useState({
    user_name: '',
    email: '',
    contact_no: '',
    payment_method: 'razorpay',
    plan_id: '',
    subscription_type: 'new_sub',
    amount: 0,
    plan_type: 'professional',
    usage_type: 'professional'
  });
  
  const [plans, setPlans] = useState([]);
  const [emailValid, setEmailValid] = useState(null);
  const [loading, setLoading] = useState(false);
  const [paymentLink, setPaymentLink] = useState(null);

  // Load plans when subscription type changes
  useEffect(() => {
    loadPlans();
  }, [formData.subscription_type]);

  const loadPlans = async () => {
    try {
      const result = await orderUserApi.getPlans(formData.subscription_type);
      if (result.success) {
        setPlans(result.data);
      }
    } catch (error) {
      console.error('Error loading plans:', error);
    }
  };

  const handleEmailBlur = async () => {
    if (!formData.email) return;
    
    try {
      const result = await orderUserApi.validateEmail(formData.email);
      
      if (!result.exists) {
        alert('Email not found in system. Please register first.');
        setEmailValid(false);
      } else if (!result.is_active) {
        alert('User account is inactive. Please activate first.');
        setEmailValid(false);
      } else {
        setEmailValid(true);
      }
    } catch (error) {
      console.error('Error validating email:', error);
      setEmailValid(false);
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    
    if (!emailValid) {
      alert('Please enter a valid email');
      return;
    }

    setLoading(true);
    
    try {
      const result = await orderUserApi.createPaymentLink(formData);
      
      if (result.success) {
        setPaymentLink(result.data.payment_link);
        
        // Copy to clipboard
        navigator.clipboard.writeText(result.data.payment_link);
        alert('Payment link created and copied to clipboard!');
      } else {
        alert(result.message);
      }
    } catch (error) {
      console.error('Error creating payment link:', error);
      alert('Failed to create payment link');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="create-payment-link">
      <h2>Create Payment Link</h2>
      
      <form onSubmit={handleSubmit}>
        <div className="form-group">
          <label>Name *</label>
          <input
            type="text"
            value={formData.user_name}
            onChange={(e) => setFormData({...formData, user_name: e.target.value})}
            required
          />
        </div>

        <div className="form-group">
          <label>Email *</label>
          <input
            type="email"
            value={formData.email}
            onChange={(e) => setFormData({...formData, email: e.target.value})}
            onBlur={handleEmailBlur}
            required
          />
          {emailValid === false && (
            <span className="error">Invalid or inactive email</span>
          )}
          {emailValid === true && (
            <span className="success">✓ Email verified</span>
          )}
        </div>

        <div className="form-group">
          <label>Contact Number *</label>
          <input
            type="tel"
            value={formData.contact_no}
            onChange={(e) => setFormData({...formData, contact_no: e.target.value})}
            required
          />
        </div>

        <div className="form-group">
          <label>Payment Method *</label>
          <select
            value={formData.payment_method}
            onChange={(e) => setFormData({...formData, payment_method: e.target.value})}
          >
            <option value="razorpay">Razorpay</option>
            <option value="phonepe">PhonePe</option>
          </select>
        </div>

        <div className="form-group">
          <label>Subscription Type *</label>
          <select
            value={formData.subscription_type}
            onChange={(e) => setFormData({...formData, subscription_type: e.target.value})}
          >
            <option value="new_sub">New Subscription</option>
            <option value="old_sub">Old Subscription</option>
          </select>
        </div>

        <div className="form-group">
          <label>Plan *</label>
          <select
            value={formData.plan_id}
            onChange={(e) => {
              const plan = plans.find(p => p.id === e.target.value);
              setFormData({
                ...formData,
                plan_id: e.target.value,
                amount: plan ? plan.price : 0
              });
            }}
            required
          >
            <option value="">Select Plan</option>
            {plans.map(plan => (
              <option key={plan.id} value={plan.id}>
                {plan.name} - ₹{plan.price}
              </option>
            ))}
          </select>
        </div>

        <div className="form-group">
          <label>Amount</label>
          <input
            type="number"
            value={formData.amount}
            readOnly
          />
        </div>

        <button 
          type="submit" 
          disabled={!emailValid || loading}
        >
          {loading ? 'Creating...' : 'Create Payment Link'}
        </button>
      </form>

      {paymentLink && (
        <div className="payment-link-result">
          <h3>Payment Link Created!</h3>
          <p>
            <a href={paymentLink} target="_blank" rel="noopener noreferrer">
              {paymentLink}
            </a>
          </p>
          <button onClick={() => navigator.clipboard.writeText(paymentLink)}>
            Copy Link
          </button>
        </div>
      )}
    </div>
  );
};

export default CreatePaymentLink;
```

---

## 🔐 Authentication

### Get Token (Use existing auth API):

```javascript
const login = async (email, password) => {
  const response = await fetch('/api/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  const data = await response.json();
  
  if (data.token) {
    localStorage.setItem('auth_token', data.token);
    return true;
  }
  
  return false;
};
```

---

## 📊 Real-time Updates

```javascript
// src/hooks/useRealtimeOrders.js
import { useState, useEffect, useRef } from 'react';
import { orderUserApi } from '../services/api/orderUserApi';

export const useRealtimeOrders = (interval = 10000) => {
  const [newOrders, setNewOrders] = useState([]);
  const lastOrderIdRef = useRef(0);

  useEffect(() => {
    const pollNewOrders = async () => {
      try {
        const result = await orderUserApi.getNewOrders(lastOrderIdRef.current);
        
        if (result.success && result.data.length > 0) {
          setNewOrders(prev => [...result.data, ...prev]);
          
          // Update last ID
          const maxId = Math.max(...result.data.map(o => o.id));
          lastOrderIdRef.current = maxId;
        }
      } catch (error) {
        console.error('Error polling new orders:', error);
      }
    };

    const intervalId = setInterval(pollNewOrders, interval);
    
    // Initial poll
    pollNewOrders();

    return () => clearInterval(intervalId);
  }, [interval]);

  const clearNewOrders = () => {
    setNewOrders([]);
  };

  return { newOrders, clearNewOrders };
};
```

---

## 🎨 UI Components Needed

### 1. Order List Page
- Filters (status, type, amount, etc.)
- Search bar
- Orders table
- Pagination
- Followup update modal

### 2. Payment Link Creation Page
- Email validation
- Plan selection
- Payment method selection
- Link generation
- Copy to clipboard

### 3. Purchase History Page
- User search
- Order history table
- Load more functionality

### 4. Dashboard (Optional)
- Real-time order notifications
- Quick stats
- Recent orders

---

## ✅ Testing Checklist

### Phase 1: Basic APIs
- [ ] Test `/followup-labels` endpoint
- [ ] Test `/get-plans` with new_sub
- [ ] Test `/get-plans` with old_sub
- [ ] Test `/validate-email` with existing email
- [ ] Test `/validate-email` with non-existing email

### Phase 2: Authenticated APIs
- [ ] Get auth token from login
- [ ] Test `/order-user` with filters
- [ ] Test `/followup-update`
- [ ] Test `/create-payment-link` with Razorpay
- [ ] Test `/create-payment-link` with PhonePe

### Phase 3: Payment Flow
- [ ] Create payment link
- [ ] Complete payment
- [ ] Check payment status
- [ ] Verify order creation

### Phase 4: Real-time
- [ ] Test `/new-orders` polling
- [ ] Verify real-time updates work

---

## 🐛 Common Issues & Solutions

### Issue 1: CORS Error
**Solution:** Backend already configured. If issue persists, check Laravel CORS config.

### Issue 2: 401 Unauthorized
**Solution:** Check if token is valid and included in Authorization header.

### Issue 3: 422 Validation Error
**Solution:** Check request body matches validation rules in documentation.

### Issue 4: Payment Link Not Working
**Solution:** 
1. Verify email exists and is active
2. Check payment gateway credentials
3. Check Laravel logs

---

## 📞 Support

### For API Issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Use Postman to test endpoints
3. Contact backend team

### For React Issues:
1. Check browser console
2. Verify API service implementation
3. Check network tab in DevTools

---

## 🎉 You're Ready!

Everything is set up and ready to use. Start with:

1. ✅ Test APIs using Postman
2. ✅ Implement API service layer
3. ✅ Create order list component
4. ✅ Add payment link creation
5. ✅ Implement real-time updates

**Good luck with your React development!** 🚀
