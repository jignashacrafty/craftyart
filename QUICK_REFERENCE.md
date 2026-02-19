# Simple Payment API - Quick Reference Card

## 🚀 Quick Start

### Create Payment Link (Minimal)
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{"amount": 100}'
```

### Check Status
```bash
curl "http://localhost/git_jignasha/craftyart/public/api/payment/status?reference_id=REF_XXX"
```

---

## 📋 API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/payment/create-link` | POST | Payment link બનાવો |
| `/api/payment/status` | GET/POST | Status check કરો |

---

## 📝 Request Format

### Minimal (Only Required)
```json
{
    "amount": 100
}
```

### Full (All Optional Fields)
```json
{
    "amount": 500,
    "email": "customer@example.com",
    "contact_no": "9876543210",
    "user_name": "Customer Name",
    "payment_method": "phonepe",
    "description": "Payment description"
}
```

---

## 📤 Response Format

### Success
```json
{
    "success": true,
    "message": "Payment link created successfully",
    "data": {
        "reference_id": "REF_A1B2C3D4E5F6",
        "payment_link": "https://phonepe.com/pay/xyz",
        "amount": 100,
        "payment_method": "phonepe",
        "status": "created"
    }
}
```

### Error
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

## 🎯 Parameters

| Parameter | Type | Required | Default | Options |
|-----------|------|----------|---------|---------|
| amount | number | ✅ Yes | - | min: 1 |
| email | string | ❌ No | customer@craftyartapp.com | valid email |
| contact_no | string | ❌ No | 9999999999 | max 15 chars |
| user_name | string | ❌ No | Customer | max 255 chars |
| payment_method | string | ❌ No | phonepe | phonepe, razorpay |
| description | string | ❌ No | Payment for services | max 500 chars |

---

## 📊 Status Values

| Status | Meaning |
|--------|---------|
| `created` | Link બની ગયું |
| `paid` | Payment સફળ |
| `failed` | Payment નિષ્ફળ |

---

## 🔍 Testing Commands

### Test 1: Minimal Data
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{"amount": 100}'
```

### Test 2: Full Data
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{
    "amount": 500,
    "email": "test@example.com",
    "contact_no": "9876543210",
    "user_name": "Test User",
    "payment_method": "phonepe"
  }'
```

### Test 3: Check Status
```bash
curl "http://localhost/git_jignasha/craftyart/public/api/payment/status?reference_id=REF_XXX"
```

### Test 4: Razorpay
```bash
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{"amount": 250, "payment_method": "razorpay"}'
```

---

## 🗂️ Files Created

```
app/Http/Controllers/Api/SimplePaymentController.php
resources/views/payment/success.blade.php
resources/views/payment/failed.blade.php
routes/api.php (updated)
routes/web.php (updated)
SIMPLE_PAYMENT_API_DOCUMENTATION.md
SIMPLE_PAYMENT_API_README_GJ.md
SIMPLE_PAYMENT_API_POSTMAN.json
test_simple_payment_api.php
```

---

## 💾 Database

**Table:** `sales`

**Key Fields:**
- reference_id
- amount
- status
- payment_method
- payment_link_url
- paid_at

---

## 🔧 Debugging

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Check Database
```sql
SELECT * FROM sales ORDER BY created_at DESC LIMIT 10;
```

### Clear Cache
```bash
php artisan route:clear
php artisan cache:clear
```

---

## ⚡ Quick Tips

1. **Minimal Data:** ફક્ત amount required છે
2. **Default Gateway:** PhonePe automatic use થાય છે
3. **Webhook Automatic:** Payment પછી automatic status update
4. **Status Check:** કોઈ પણ સમયે check કરી શકાય
5. **Multiple Gateways:** PhonePe અને Razorpay બંને support

---

## 🎯 Common Use Cases

### Use Case 1: Quick Payment
```json
{"amount": 100}
```

### Use Case 2: Custom Payment
```json
{
    "amount": 500,
    "email": "customer@example.com",
    "description": "Premium subscription"
}
```

### Use Case 3: Razorpay Payment
```json
{
    "amount": 250,
    "payment_method": "razorpay"
}
```

---

## 📱 Integration Example

```javascript
// JavaScript/AJAX
fetch('/api/payment/create-link', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({amount: 100})
})
.then(r => r.json())
.then(data => {
    if (data.success) {
        window.location.href = data.data.payment_link;
    }
});
```

---

## ✅ Checklist

- [ ] API endpoint working
- [ ] Payment link generating
- [ ] Webhook receiving callbacks
- [ ] Status updating correctly
- [ ] Success page showing
- [ ] Failed page showing
- [ ] Logs recording properly
- [ ] Database storing data

---

## 📞 Need Help?

1. Check `SIMPLE_PAYMENT_API_README_GJ.md` for detailed guide
2. Check `SIMPLE_PAYMENT_API_DOCUMENTATION.md` for API docs
3. Import `SIMPLE_PAYMENT_API_POSTMAN.json` in Postman
4. Run `test_simple_payment_api.php` for automated tests
5. Check logs: `storage/logs/laravel.log`

---

**Ready to use! 🚀**
