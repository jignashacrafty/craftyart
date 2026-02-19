# Simple Payment API Documentation

## Overview
Ek simple ane common payment link generation API je minimal data sathe payment link banave. PhonePe ane Razorpay banne payment methods support kare che.

## Base URL
```
http://localhost/git_jignasha/craftyart/public/api
```

## API Endpoints

### 1. Create Payment Link

**Endpoint:** `POST /api/payment/create-link`

**Description:** Payment link generate kare che with minimal data

**Request Body:**
```json
{
    "amount": 100,
    "email": "customer@example.com",
    "contact_no": "9876543210",
    "user_name": "John Doe",
    "payment_method": "phonepe",
    "description": "Payment for subscription"
}
```

**Parameters:**
- `amount` (required): Payment amount in INR (minimum 1)
- `email` (optional): Customer email (default: customer@craftyartapp.com)
- `contact_no` (optional): Customer contact number (default: 9999999999)
- `user_name` (optional): Customer name (default: Customer)
- `payment_method` (optional): Payment gateway - `phonepe` or `razorpay` (default: phonepe)
- `description` (optional): Payment description (default: Payment for services)

**Success Response:**
```json
{
    "success": true,
    "message": "Payment link created successfully",
    "data": {
        "reference_id": "REF_A1B2C3D4E5F6",
        "payment_link": "https://phonepe.com/pay/xyz123",
        "amount": 100,
        "payment_method": "phonepe",
        "status": "created"
    }
}
```

**Error Response:**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount field is required."]
    }
}
```

---

### 2. Check Payment Status

**Endpoint:** `GET /api/payment/status` or `POST /api/payment/status`

**Description:** Payment ni status check kare che

**Request Parameters:**
```
reference_id: REF_A1B2C3D4E5F6
```

**Success Response:**
```json
{
    "success": true,
    "data": {
        "reference_id": "REF_A1B2C3D4E5F6",
        "amount": 100,
        "status": "paid",
        "payment_method": "phonepe",
        "paid_at": "2026-02-18 10:30:00",
        "order_id": 12345
    }
}
```

**Status Values:**
- `created` - Payment link created
- `paid` - Payment successful
- `failed` - Payment failed

---

### 3. Razorpay Webhook

**Endpoint:** `GET/POST /api/payment/razorpay-webhook`

**Description:** Razorpay callback handler (automatically called by Razorpay)

**Note:** This endpoint is called automatically by Razorpay after payment. No manual testing needed.

---

### 4. PhonePe Webhook

**Endpoint:** `GET/POST /api/payment/phonepe-webhook`

**Description:** PhonePe callback handler (automatically called by PhonePe)

**Note:** This endpoint is called automatically by PhonePe after payment. No manual testing needed.

---

## Payment Flow

### Complete Flow:

1. **Create Payment Link**
   ```
   POST /api/payment/create-link
   Body: { amount: 100, payment_method: "phonepe" }
   ```
   
2. **Get Payment Link**
   ```
   Response: { payment_link: "https://phonepe.com/pay/xyz" }
   ```

3. **Customer Pays**
   - Customer opens payment link
   - Completes payment on PhonePe/Razorpay

4. **Webhook Called**
   - PhonePe/Razorpay automatically calls webhook
   - Status updated to "paid"

5. **Check Status**
   ```
   GET /api/payment/status?reference_id=REF_A1B2C3D4E5F6
   Response: { status: "paid" }
   ```

---

## Testing Examples

### Example 1: PhonePe Payment (Minimal Data)
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 100
  }'
```

### Example 2: Razorpay Payment (Full Data)
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 500,
    "email": "test@example.com",
    "contact_no": "9876543210",
    "user_name": "Test User",
    "payment_method": "razorpay",
    "description": "Test payment for subscription"
  }'
```

### Example 3: Check Payment Status
```bash
curl -X GET "http://localhost/git_jignasha/craftyart/public/api/payment/status?reference_id=REF_A1B2C3D4E5F6"
```

---

## Postman Collection

### Create Payment Link Request
```
Method: POST
URL: http://localhost/git_jignasha/craftyart/public/api/payment/create-link
Headers:
  Content-Type: application/json
Body (raw JSON):
{
    "amount": 100,
    "email": "test@example.com",
    "contact_no": "9876543210",
    "user_name": "Test User",
    "payment_method": "phonepe",
    "description": "Test payment"
}
```

### Check Status Request
```
Method: GET
URL: http://localhost/git_jignasha/craftyart/public/api/payment/status?reference_id={{reference_id}}
```

---

## Database Tables

### sales table
Payment link ni details store thay che:
- `reference_id` - Unique reference ID
- `amount` - Payment amount
- `status` - Payment status (created/paid/failed)
- `payment_method` - phonepe/razorpay
- `payment_link_url` - Generated payment link
- `paid_at` - Payment completion timestamp

---

## Error Codes

| Code | Message | Description |
|------|---------|-------------|
| 422 | Validation failed | Invalid request parameters |
| 404 | Payment not found | Reference ID not found |
| 500 | Internal server error | Server error during processing |

---

## Notes

1. **Minimal Data Required:** Sirf `amount` required che, baaki badha fields optional che
2. **Default Values:** Email, contact, name automatic set thay jay if not provided
3. **Webhook Automatic:** Payment success/fail thi automatic webhook call thay
4. **Status Check:** Anytime payment status check kari shakay
5. **Multiple Gateways:** PhonePe ane Razorpay banne support che

---

## Support

For any issues or questions:
- Check logs: `storage/logs/laravel.log`
- Database: Check `sales` table for payment records
- Webhook logs: All webhook calls logged automatically
