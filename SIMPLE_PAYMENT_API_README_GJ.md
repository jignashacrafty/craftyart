# Simple Payment API - સરળ ઉપયોગ માર્ગદર્શિકા

## શું છે આ API?

આ એક સરળ payment link generation API છે જે ખૂબ જ ઓછા data સાથે payment link બનાવે છે. તમારે ફક્ત amount આપવાની જરૂર છે, બાકી બધું automatic handle થઈ જાય છે.

## મુખ્ય Features

✅ **Minimal Data** - ફક્ત amount required છે  
✅ **Multiple Gateways** - PhonePe અને Razorpay બંને support કરે છે  
✅ **Automatic Webhook** - Payment થયા પછી automatic status update થાય છે  
✅ **Status Check** - કોઈ પણ સમયે payment status check કરી શકાય છે  
✅ **Default Values** - Email, contact, name automatic set થાય છે જો provide ન કરો તો  

## API Endpoints

### 1️⃣ Payment Link બનાવો

```
POST /api/payment/create-link
```

**સૌથી સરળ રીત (ફક્ત amount):**
```json
{
    "amount": 100
}
```

**સંપૂર્ણ details સાથે:**
```json
{
    "amount": 500,
    "email": "customer@example.com",
    "contact_no": "9876543210",
    "user_name": "Customer Name",
    "payment_method": "phonepe",
    "description": "Subscription payment"
}
```

**Response:**
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

### 2️⃣ Payment Status Check કરો

```
GET /api/payment/status?reference_id=REF_A1B2C3D4E5F6
```

**Response:**
```json
{
    "success": true,
    "data": {
        "reference_id": "REF_A1B2C3D4E5F6",
        "amount": 100,
        "status": "paid",
        "payment_method": "phonepe",
        "paid_at": "2026-02-18 10:30:00"
    }
}
```

## કેવી રીતે કામ કરે છે?

### સંપૂર્ણ Flow:

```
1. API Call કરો
   ↓
2. Payment Link મળે છે
   ↓
3. Customer Payment કરે છે
   ↓
4. Webhook Automatic Call થાય છે
   ↓
5. Status "paid" થઈ જાય છે
   ↓
6. તમે Status Check કરી શકો છો
```

## Testing કેવી રીતે કરવું?

### Postman માં Test કરો:

1. **Postman Collection Import કરો:**
   - File: `SIMPLE_PAYMENT_API_POSTMAN.json`
   - Import કરો Postman માં

2. **Base URL Set કરો:**
   ```
   http://localhost/git_jignasha/craftyart/public/api
   ```

3. **Payment Link Create કરો:**
   - Request: "1. Create Payment Link - PhonePe (Minimal)"
   - Send કરો
   - Response માં payment_link મળશે

4. **Status Check કરો:**
   - Request: "4. Check Payment Status (GET)"
   - Send કરો
   - Current status જોવા મળશે

### cURL થી Test કરો:

```bash
# Payment Link Create કરો
curl -X POST http://localhost/git_jignasha/craftyart/public/api/payment/create-link \
  -H "Content-Type: application/json" \
  -d '{"amount": 100}'

# Status Check કરો
curl -X GET "http://localhost/git_jignasha/craftyart/public/api/payment/status?reference_id=REF_A1B2C3D4E5F6"
```

## Parameters સમજો

### Required (જરૂરી):
- `amount` - Payment amount (minimum 1 રૂપિયા)

### Optional (વૈકલ્પિક):
- `email` - Customer email (default: customer@craftyartapp.com)
- `contact_no` - Contact number (default: 9999999999)
- `user_name` - Customer name (default: Customer)
- `payment_method` - Gateway: `phonepe` અથવા `razorpay` (default: phonepe)
- `description` - Payment description (default: Payment for services)

## Payment Status Values

| Status | અર્થ |
|--------|------|
| `created` | Payment link બની ગયું છે |
| `paid` | Payment સફળ થયું છે |
| `failed` | Payment નિષ્ફળ થયું છે |

## Database માં Data

Payment details `sales` table માં store થાય છે:

```sql
SELECT * FROM sales WHERE reference_id = 'REF_A1B2C3D4E5F6';
```

**Important Fields:**
- `reference_id` - Unique ID
- `amount` - Payment amount
- `status` - Payment status
- `payment_link_url` - Generated link
- `paid_at` - Payment time

## Webhook કેવી રીતે કામ કરે છે?

જ્યારે customer payment કરે છે, PhonePe/Razorpay automatic આ URLs call કરે છે:

- **Razorpay:** `/api/payment/razorpay-webhook`
- **PhonePe:** `/api/payment/phonepe-webhook`

આ automatic થાય છે, તમારે કંઈ કરવાની જરૂર નથી!

## Error Handling

### Common Errors:

**1. Validation Error (422):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "amount": ["The amount field is required."]
    }
}
```

**2. Payment Not Found (404):**
```json
{
    "success": false,
    "message": "Payment not found"
}
```

**3. Server Error (500):**
```json
{
    "success": false,
    "message": "Error creating payment link: ..."
}
```

## Logs કેવી રીતે જોવા?

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Payment creation logs જોવા
grep "Simple Payment Link Creation" storage/logs/laravel.log

# Webhook logs જોવા
grep "Webhook Received" storage/logs/laravel.log
```

## Live Server પર Deploy કરવા માટે

1. **Files Upload કરો:**
   - `app/Http/Controllers/Api/SimplePaymentController.php`
   - `resources/views/payment/success.blade.php`
   - `resources/views/payment/failed.blade.php`

2. **Routes Update કરો:**
   - `routes/api.php` માં payment routes add કરો
   - `routes/web.php` માં success/failed routes add કરો

3. **Cache Clear કરો:**
   ```bash
   php artisan route:clear
   php artisan cache:clear
   php artisan config:clear
   ```

4. **Test કરો:**
   ```bash
   curl -X POST https://your-domain.com/api/payment/create-link \
     -H "Content-Type: application/json" \
     -d '{"amount": 100}'
   ```

## Important Notes

⚠️ **Payment Gateway Configuration:**
- PhonePe અને Razorpay credentials `payment_configurations` table માં હોવા જોઈએ
- Credentials active હોવા જોઈએ (`is_active = 1`)

⚠️ **Webhook URLs:**
- Live server પર webhook URLs correct set કરો
- PhonePe/Razorpay dashboard માં webhook URLs configure કરો

⚠️ **Testing:**
- Sandbox/UAT environment માં પહેલા test કરો
- Production માં જતા પહેલા બધા scenarios test કરો

## Support

**Issues થાય તો:**
1. Logs check કરો: `storage/logs/laravel.log`
2. Database check કરો: `sales` table
3. Payment gateway credentials verify કરો
4. Webhook URLs verify કરો

## Example Use Cases

### Use Case 1: Quick Payment Link
```json
POST /api/payment/create-link
{
    "amount": 100
}
```
→ Instant payment link મળે છે

### Use Case 2: Custom Payment with Details
```json
POST /api/payment/create-link
{
    "amount": 500,
    "email": "customer@example.com",
    "user_name": "John Doe",
    "description": "Premium subscription"
}
```
→ Detailed payment link with customer info

### Use Case 3: Check Multiple Payments
```bash
# Loop through reference IDs
for ref_id in REF_001 REF_002 REF_003; do
    curl "http://localhost/api/payment/status?reference_id=$ref_id"
done
```

## આગળ શું?

આ API તમે તમારા application માં integrate કરી શકો છો:
- Mobile app માં
- Website માં
- Admin panel માં
- Third-party services માં

**Happy Coding! 🚀**
