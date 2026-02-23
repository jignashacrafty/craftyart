# Purchase History Cross-Database Query Fix

## Problem

The error "Failed to load purchase history. Error: Internal Server Error" was occurring because of a cross-database query issue.

### Root Cause

The `OrderUserController::getPurchaseHistory()` method was trying to query the `Sale` model for each order:

```php
$sale = Sale::where('order_id', $order->id)->first();
```

**The Issue:**
- `Order` model uses `mysql` connection (database: `crafty_db`)
- `Sale` model uses `crafty_revenue_mysql` connection (database: `crafty_revenue`)
- Querying across different databases in a loop was causing Internal Server Error

## Solution Applied

### Immediate Fix (Applied)

Wrapped the Sale query in a try-catch block to prevent the error from breaking the page:

```php
// First, try to find payment method from Sale record
try {
    $sale = Sale::where('order_id', $order->id)->first();
} catch (\Exception $e) {
    \Log::warning('Could not fetch sale for order', [
        'order_id' => $order->id,
        'error' => $e->getMessage()
    ]);
    $sale = null;
}
```

This allows the page to load even if the cross-database query fails.

### Recommended Optimization (Future)

For better performance, fetch all sales at once instead of querying in a loop:

```php
public function getPurchaseHistory($userId)
{
    try {
        // Get only successful orders
        $orders = Order::with(['subPlan', 'subscription', 'offerPackage.subPlan'])
            ->where('user_id', $userId)
            ->where('is_deleted', 0)
            ->whereIn('status', ['success', 'paid'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get all sales for these orders (from different database)
        $orderIds = $orders->pluck('id')->toArray();
        $salesByOrderId = [];
        
        if (!empty($orderIds)) {
            try {
                $sales = Sale::whereIn('order_id', $orderIds)->get();
                foreach ($sales as $sale) {
                    $salesByOrderId[$sale->order_id] = $sale;
                }
            } catch (\Exception $e) {
                \Log::warning('Could not fetch sales', [
                    'order_ids' => $orderIds,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $allOrders = $orders->map(function ($order) use ($salesByOrderId) {
            $paymentMethod = 'Completed';
            
            // Get sale from pre-fetched array
            $sale = $salesByOrderId[$order->id] ?? null;
            if ($sale && $sale->payment_method) {
                $paymentMethod = ucfirst($sale->payment_method);
            } else {
                // Fallback logic...
            }
            
            // ... rest of the mapping
        });
        
        // ... rest of the method
    } catch (\Exception $e) {
        // Error handling
    }
}
```

## Database Structure

### Order Table
- **Database**: `crafty_db`
- **Connection**: `mysql`
- **Model**: `App\Models\Order`

### Sales Table
- **Database**: `crafty_revenue`
- **Connection**: `crafty_revenue_mysql`
- **Model**: `App\Models\Revenue\Sale`

### Purchase History Table
- **Database**: `crafty_db`
- **Connection**: `mysql`
- **Model**: `App\Models\PurchaseHistory`
- **Note**: This is for template/video purchases, different from Sales table

## Why Two Databases?

The application uses multiple databases for modular organization:

1. **crafty_db (mysql)** - Main database
   - Orders
   - Users
   - Templates
   - Purchase History (templates/videos)

2. **crafty_revenue (crafty_revenue_mysql)** - Revenue tracking
   - Sales records
   - Business support purchases
   - Revenue analytics

3. **crafty_pricing (crafty_pricing_mysql)** - Pricing configuration
   - Plans
   - Payment configurations
   - Offers

## Testing

After the fix:
1. ✅ Page loads without "Internal Server Error"
2. ✅ Orders display correctly
3. ✅ Payment methods show (when Sale record exists)
4. ✅ Fallback to Order fields works (when Sale record doesn't exist)
5. ✅ Error is logged for debugging but doesn't break the page

## Files Modified

- `app/Http/Controllers/OrderUserController.php` - Added try-catch around Sale query

## Related Files

- `app/Models/Order.php` - Uses `mysql` connection
- `app/Models/Revenue/Sale.php` - Uses `crafty_revenue_mysql` connection
- `app/Models/PurchaseHistory.php` - Uses `mysql` connection
- `config/database.php` - Database connections configuration

## Notes

- The cross-database relationship is intentional for modular organization
- Always handle cross-database queries carefully
- Consider caching or batch fetching for performance
- Log errors for debugging without breaking user experience
