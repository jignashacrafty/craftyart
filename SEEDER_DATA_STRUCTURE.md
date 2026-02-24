# Purchase History & User Subscriptions - Data Structure

## Overview
This seeder creates 10 entries with proper relationships between:
- `purchase_history` table (crafty_revenue database)
- `manage_subscriptions` table (mysql connection)
- Existing `user_data` table
- Existing `subscriptions` table

## Data Flow

```
user_data (existing)
    ↓
    └─→ purchase_history (new entries)
            ├─→ Links to: subscriptions.id (product_id)
            ├─→ Links to: user_data.uid (user_id)
            └─→ If payment_status = 1 (success)
                    ↓
                    └─→ manage_subscriptions (new entry)
                            └─→ Links to: user_data.uid (user_id)
```

## Sample Data Generated

### User 1: Successful Monthly Plan Purchase
```
purchase_history:
├─ user_id: "USER001" (from user_data)
├─ product_id: 2 (Crafty Art Pro - Monthly)
├─ transaction_id: "TXN1708876543001"
├─ payment_id: "PAY1708876543001"
├─ amount: 1499.00
├─ payment_method: "PhonePe"
├─ payment_status: 1 (Success)
├─ phonepe_subscription_id: "SUB_1708876543_1"
├─ is_autopay_enabled: 1
├─ autopay_status: "ACTIVE"
└─ created_at: 2026-02-20 10:30:00

manage_subscriptions:
├─ user_id: "USER001"
├─ package_name: "Crafty Art Pro - Monthly"
├─ validity: 30 days
├─ price: 1499.00
├─ months: 1
└─ status: 1 (Active)
```

### User 2: Failed 6 Months Plan Purchase
```
purchase_history:
├─ user_id: "USER002" (from user_data)
├─ product_id: 3 (Crafty Art Pro - 6 Months)
├─ transaction_id: "TXN1708876543002"
├─ payment_id: "PAY1708876543002"
├─ amount: 6999.00
├─ payment_method: "Razorpay"
├─ payment_status: 0 (Failed)
└─ created_at: 2026-02-19 15:45:00

manage_subscriptions:
└─ (No entry - payment failed)
```

### User 3: Successful Yearly Plan with AutoPay
```
purchase_history:
├─ user_id: "USER003" (from user_data)
├─ product_id: 4 (Crafty Art Pro - Yearly)
├─ transaction_id: "TXN1708876543003"
├─ payment_id: "PAY1708876543003"
├─ amount: 11999.00
├─ payment_method: "PhonePe"
├─ payment_status: 1 (Success)
├─ phonepe_merchant_order_id: "MERCHANT_1708876543_3"
├─ phonepe_subscription_id: "SUB_1708876543_3"
├─ is_autopay_enabled: 1
├─ autopay_status: "ACTIVE"
├─ autopay_activated_at: 2026-02-18 09:15:00
├─ next_autopay_date: 2027-02-18
├─ autopay_count: 0
└─ created_at: 2026-02-18 09:15:00

manage_subscriptions:
├─ user_id: "USER003"
├─ package_name: "Crafty Art Pro - Yearly"
├─ validity: 365 days
├─ price: 11999.00
├─ months: 12
└─ status: 1 (Active)
```

## Field Mappings

### purchase_history Table
| Field | Source | Example |
|-------|--------|---------|
| user_id | user_data.uid | "USER001" |
| product_id | subscriptions.id | 2 |
| product_type | Fixed | 1 (subscription) |
| transaction_id | Generated | "TXN1708876543001" |
| payment_id | Generated | "PAY1708876543001" |
| currency_code | Fixed | "INR" |
| amount | subscriptions.price | 1499.00 |
| payment_method | Random | PhonePe, Razorpay, UPI, Card, NetBanking |
| from_where | Random | Web, Mobile |
| contact_no | Generated | "9876543200" |
| payment_status | Random (80% success) | 1 or 0 |
| status | Same as payment_status | 1 or 0 |

### PhonePe AutoPay Fields (50% of PhonePe payments)
| Field | Example |
|-------|---------|
| phonepe_merchant_order_id | "MERCHANT_1708876543_1" |
| phonepe_subscription_id | "SUB_1708876543_1" |
| phonepe_order_id | "ORDER_1708876543_1" |
| phonepe_transaction_id | "PHONEPE_TXN_1708876543_1" |
| is_autopay_enabled | 1 or 0 |
| autopay_status | "ACTIVE" or NULL |
| autopay_activated_at | "2026-02-20 10:30:00" or NULL |
| next_autopay_date | "2026-03-22" or NULL |
| autopay_count | 0-5 |

### manage_subscriptions Table
| Field | Source | Example |
|-------|--------|---------|
| user_id | user_data.uid | "USER001" |
| package_name | subscriptions.package_name | "Crafty Art Pro - Monthly" |
| desc | subscriptions.desc | "Utilize Your Endless..." |
| validity | subscriptions.validity | 30 |
| actual_price | subscriptions.actual_price | 1999.00 |
| price | subscriptions.price | 1499.00 |
| months | subscriptions.months | 1 |
| has_offer | subscriptions.has_offer | 1 |
| status | Fixed | 1 (Active) |

## Statistics (Expected)

### Total Entries
- Purchase History: 10 entries
- User Subscriptions: ~8 entries (80% success rate)

### Payment Methods Distribution (Approximate)
- PhonePe: 2-3 entries
- Razorpay: 2-3 entries
- UPI: 2 entries
- Card: 1-2 entries
- NetBanking: 1-2 entries

### Payment Status Distribution
- Successful (status=1): ~8 entries (80%)
- Failed (status=0): ~2 entries (20%)

### Plan Distribution (Random)
- Monthly Plan: 2-4 entries
- 6 Months Plan: 2-4 entries
- Yearly Plan: 2-4 entries

### AutoPay Distribution
- PhonePe with AutoPay: 1-2 entries
- PhonePe without AutoPay: 1-2 entries
- Other methods: 6-8 entries

## Validation Checks

### Before Seeding
```sql
-- Check users exist
SELECT COUNT(*) FROM user_data;
-- Should return: >= 10

-- Check plans exist
SELECT COUNT(*) FROM subscriptions WHERE package_name != 'Free';
-- Should return: >= 3
```

### After Seeding
```sql
-- Verify purchase history
SELECT COUNT(*) FROM purchase_history;
-- Should return: 10

-- Verify user subscriptions
SELECT COUNT(*) FROM manage_subscriptions;
-- Should return: ~8 (80% of 10)

-- Verify relationships
SELECT 
    COUNT(DISTINCT ph.user_id) as unique_users,
    COUNT(ph.id) as total_purchases,
    COUNT(ms.id) as total_subscriptions
FROM purchase_history ph
LEFT JOIN manage_subscriptions ms ON ph.user_id = ms.user_id;
-- unique_users: 10
-- total_purchases: 10
-- total_subscriptions: ~8
```

## Database Connections

### purchase_history
- Connection: Default (crafty_revenue)
- Table: `purchase_history`

### manage_subscriptions
- Connection: `mysql`
- Table: `manage_subscriptions`

### user_data
- Connection: Default (crafty_revenue)
- Table: `user_data`

### subscriptions
- Connection: `mysql`
- Table: `subscriptions`

## Important Notes

1. **User Dependency**: Seeder requires existing users in `user_data` table
2. **Plan Dependency**: Seeder requires existing plans in `subscriptions` table
3. **Success Rate**: 80% payments are successful, 20% failed
4. **Subscription Creation**: Only successful payments create subscription entries
5. **AutoPay**: Only PhonePe payments can have AutoPay enabled (50% chance)
6. **Date Range**: Purchases are randomly distributed over last 30 days
7. **Unique IDs**: Transaction and payment IDs are unique per entry

## Testing Scenarios Covered

✅ Successful payment with subscription creation
✅ Failed payment without subscription creation
✅ PhonePe payment with AutoPay enabled
✅ PhonePe payment without AutoPay
✅ Multiple payment methods
✅ Different subscription plans
✅ Web and Mobile purchases
✅ Recent purchase dates (last 30 days)
✅ Proper foreign key relationships
✅ Realistic transaction IDs
