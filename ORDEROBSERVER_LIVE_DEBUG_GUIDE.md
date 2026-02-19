# 🔍 OrderObserver Live Server Debug Guide

## 📌 સમસ્યા
OrderObserver local માં કામ કરે છે પણ live server પર કામ નથી કરતું.

---

## ✅ Step-by-Step Debugging

### Step 1: Check કરો કે Observer Registered છે કે નહીં

**File:** `app/Providers/AppServiceProvider.php`

```php
public function boot()
{
    // આ line હોવી જોઈએ
    \App\Models\Order::observe(\App\Observers\OrderObserver::class);
}
```

**Live server પર check કરો:**
```bash
cd /path/to/your/project
cat app/Providers/AppServiceProvider.php | grep OrderObserver
```

જો આ line ન હોય તો add કરો અને cache clear કરો:
```bash
php artisan config:clear
php artisan cache:clear
```

---

### Step 2: Check કરો કે BROADCAST_DRIVER Correct છે

**File:** `.env`

```env
# આ હોવું જોઈએ:
BROADCAST_DRIVER=pusher

# આ ન હોવું જોઈએ:
# BROADCAST_DRIVER=log
# BROADCAST_DRIVER=null
```

**Live server પર check કરો:**
```bash
cat .env | grep BROADCAST_DRIVER
```

જો wrong હોય તો correct કરો:
```bash
# .env file edit કરો
nano .env

# BROADCAST_DRIVER=pusher set કરો
# Save કરો (Ctrl+X, Y, Enter)

# Cache clear કરો
php artisan config:clear
```

---

### Step 3: Check કરો કે WebSocket Server Running છે

```bash
# Check કરો
ps aux | grep websockets:serve

# જો running ન હોય તો start કરો
nohup php artisan websockets:serve > /dev/null 2>&1 &
```

---

### Step 4: Check કરો કે Observer Actually Call થાય છે કે નહીં

**Test કરવા માટે આ script બનાવો:**

**File:** `test_observer_live.php`

```php
<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🧪 Testing OrderObserver on Live Server\n";
echo "========================================\n\n";

// Test 1: Check if Observer is registered
echo "1️⃣  Checking if OrderObserver is registered...\n";
$observers = \App\Models\Order::getObservableEvents();
echo "   Observable events: " . implode(', ', $observers) . "\n";

// Test 2: Check BROADCAST_DRIVER
echo "\n2️⃣  Checking BROADCAST_DRIVER...\n";
$driver = config('broadcasting.default');
echo "   BROADCAST_DRIVER: {$driver}\n";

if ($driver !== 'pusher') {
    echo "   ❌ ERROR: BROADCAST_DRIVER should be 'pusher', not '{$driver}'\n";
    echo "   Fix: Set BROADCAST_DRIVER=pusher in .env file\n";
    exit(1);
}

// Test 3: Check Pusher configuration
echo "\n3️⃣  Checking Pusher configuration...\n";
$pusherHost = config('broadcasting.connections.pusher.options.host');
$pusherPort = config('broadcasting.connections.pusher.options.port');
$pusherKey = config('broadcasting.connections.pusher.key');
echo "   PUSHER_HOST: {$pusherHost}\n";
echo "   PUSHER_PORT: {$pusherPort}\n";
echo "   PUSHER_KEY: {$pusherKey}\n";

// Test 4: Check if WebSocket server is reachable
echo "\n4️⃣  Checking WebSocket server connectivity...\n";
$url = "http://{$pusherHost}:{$pusherPort}/app/{$pusherKey}";
echo "   Testing URL: {$url}\n";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode === 404 || $httpCode === 200) {
    echo "   ✅ WebSocket server is reachable (HTTP {$httpCode})\n";
} else {
    echo "   ❌ WebSocket server is NOT reachable (HTTP {$httpCode})\n";
    echo "   Response: {$response}\n";
    echo "   Fix: Start WebSocket server with: php artisan websockets:serve\n";
}

// Test 5: Create a test order and check if Observer fires
echo "\n5️⃣  Creating test order to check Observer...\n";

try {
    // Enable query log
    \DB::enableQueryLog();
    
    $testOrder = new \App\Models\Order();
    $testOrder->user_id = 'test_user_' . time();
    $testOrder->plan_id = 'test_plan';
    $testOrder->contact_no = '9999999999';
    $testOrder->crafty_id = 'test_' . time();
    $testOrder->razorpay_order_id = 'test_razorpay_' . time();
    $testOrder->status = 'pending';
    $testOrder->amount = '999';
    $testOrder->currency = 'INR';
    $testOrder->type = 'new_sub';
    $testOrder->is_deleted = 0;
    
    echo "   Saving test order...\n";
    $testOrder->save();
    
    echo "   ✅ Test order created with ID: {$testOrder->id}\n";
    echo "   Check logs: tail -f storage/logs/laravel.log\n";
    echo "   Look for: 'OrderObserver: Broadcasting new order'\n";
    
    // Clean up test order
    echo "\n6️⃣  Cleaning up test order...\n";
    $testOrder->delete();
    echo "   ✅ Test order deleted\n";
    
} catch (\Exception $e) {
    echo "   ❌ ERROR creating test order: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . "\n";
    echo "   Line: " . $e->getLine() . "\n";
}

echo "\n========================================\n";
echo "✅ Testing Complete!\n\n";

echo "📋 Next Steps:\n";
echo "1. Check Laravel logs: tail -f storage/logs/laravel.log\n";
echo "2. Look for 'OrderObserver' messages\n";
echo "3. If no logs, Observer is not firing\n";
echo "4. If logs present but no WebSocket, check WebSocket server\n";
```

**Live server પર આ script ચલાવો:**
```bash
php test_observer_live.php
```

---

### Step 5: Check Laravel Logs

```bash
# Live server પર logs જુઓ
tail -f storage/logs/laravel.log

# આ messages જોવા જોઈએ:
# OrderObserver: Broadcasting new order
# WebSocketBroadcast: Sending direct HTTP API request
# WebSocketBroadcast: Direct HTTP API success
```

જો આ messages ન દેખાય તો Observer fire નથી થતું!

---

## 🔧 Common Issues અને Solutions

### Issue 1: Observer Fire નથી થતું

**કારણ:** Observer registered નથી અથવા cache issue

**Solution:**
```bash
# Cache clear કરો
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Composer autoload regenerate કરો
composer dump-autoload

# Application restart કરો (if using PHP-FPM)
sudo service php8.1-fpm restart
```

### Issue 2: Observer Fire થાય છે પણ WebSocket Event Send નથી થતું

**કારણ:** WebSocket server running નથી અથવા wrong configuration

**Solution:**
```bash
# WebSocket server check કરો
ps aux | grep websockets:serve

# જો running ન હોય તો start કરો
nohup php artisan websockets:serve > /dev/null 2>&1 &

# .env માં PUSHER_HOST check કરો
cat .env | grep PUSHER_HOST

# તમારું domain/IP હોવું જોઈએ, 127.0.0.1 નહીં!
```

### Issue 3: BROADCAST_DRIVER Wrong છે

**કારણ:** .env માં BROADCAST_DRIVER=log અથવા null છે

**Solution:**
```bash
# .env edit કરો
nano .env

# આ line શોધો અને બદલો:
BROADCAST_DRIVER=pusher

# Save કરો અને cache clear કરો
php artisan config:clear
```

### Issue 4: Order Create થાય છે પણ Observer Call નથી થતું

**કારણ:** Order create થતો નથી Eloquent ORM થી (raw query વાપરી રહ્યા છો)

**Solution:**

Observer માત્ર Eloquent ORM થી create થતા orders માટે જ fire થાય છે.

**Wrong (Observer fire નહીં થાય):**
```php
DB::table('orders')->insert([...]);  // ❌ Raw query
```

**Correct (Observer fire થશે):**
```php
Order::create([...]);  // ✅ Eloquent
$order = new Order();
$order->save();  // ✅ Eloquent
```

---

## 🧪 Manual Testing

### Test 1: Manually Trigger Observer

```bash
php artisan tinker

# આ commands ચલાવો:
$order = App\Models\Order::first();
event(new App\Events\NewOrderCreated($order));

# Check logs:
# exit
tail -f storage/logs/laravel.log
```

### Test 2: Check WebSocket Directly

```bash
# WebSocket API ને directly call કરો
curl -X POST "http://your-domain.com:6001/apps/local/events?auth_key=localkey&auth_timestamp=$(date +%s)&auth_version=1.0&body_md5=$(echo -n '{"name":"test","channels":["orders"],"data":"{}"}' | md5sum | cut -d' ' -f1)&auth_signature=test" \
  -H "Content-Type: application/json" \
  -d '{"name":"test","channels":["orders"],"data":"{}"}'
```

---

## 📊 Debugging Checklist

Live server પર આ બધું check કરો:

- [ ] `app/Providers/AppServiceProvider.php` માં Observer registered છે
- [ ] `.env` માં `BROADCAST_DRIVER=pusher` છે
- [ ] `.env` માં `PUSHER_HOST=your-domain.com` (correct domain/IP)
- [ ] WebSocket server running છે (`ps aux | grep websockets:serve`)
- [ ] Port 6001 open છે (`netstat -tulpn | grep 6001`)
- [ ] Cache clear કર્યું છે (`php artisan config:clear`)
- [ ] Logs માં Observer messages દેખાય છે
- [ ] Orders Eloquent ORM થી create થાય છે (raw queries નહીં)

---

## 🚀 Quick Fix Commands

જો કંઈ કામ ન કરે તો આ બધા commands એક સાથે ચલાવો:

```bash
# 1. Cache clear કરો
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 2. Composer autoload regenerate કરો
composer dump-autoload

# 3. WebSocket server restart કરો
pkill -f "websockets:serve"
nohup php artisan websockets:serve > /dev/null 2>&1 &

# 4. Check કરો કે બધું running છે
ps aux | grep websockets:serve
netstat -tulpn | grep 6001

# 5. Test order create કરો
php test_observer_live.php

# 6. Logs જુઓ
tail -f storage/logs/laravel.log
```

---

## 💡 Pro Tip

Observer debug કરવા માટે temporary logging add કરો:

**File:** `app/Observers/OrderObserver.php`

```php
public function created(Order $order)
{
    // આ line add કરો debugging માટે
    \Log::info('🔥 OrderObserver::created() CALLED!', [
        'order_id' => $order->id,
        'status' => $order->status,
        'broadcast_driver' => config('broadcasting.default'),
        'pusher_host' => config('broadcasting.connections.pusher.options.host'),
    ]);
    
    // બાકીનો code...
}
```

હવે logs માં આ message દેખાશે જ્યારે પણ order create થશે!

---

## 📞 Still Not Working?

જો હજુ પણ કામ ન કરે તો:

1. **Logs check કરો:** `tail -f storage/logs/laravel.log`
2. **Test script ચલાવો:** `php test_observer_live.php`
3. **WebSocket server logs:** Check if any errors
4. **PHP-FPM restart:** `sudo service php8.1-fpm restart`
5. **Server restart:** Last resort - restart entire server

---

**Remember:** Local માં કામ કરે છે તો code correct છે. Issue configuration અથવા environment નો છે! 🎯
