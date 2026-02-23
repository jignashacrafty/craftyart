# WebSocket Production Setup Guide

## Current Status
Both `order_user` and `recent_expire` pages are already configured to use dynamic WebSocket settings from `.env` file. No code changes needed!

## Configuration Files

### Local Development (.env)
```env
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=localkey
PUSHER_APP_SECRET=localsecret

PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

### Production (.env on live server)
```env
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=localkey
PUSHER_APP_SECRET=localsecret

# ⚠️ IMPORTANT: Change this to your live domain or IP
PUSHER_HOST=craftyart.in
# OR use IP address:
# PUSHER_HOST=123.45.67.89

PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

## How It Works

Both pages use this configuration:
```javascript
const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
    wsHost: '{{ env('PUSHER_HOST', '127.0.0.1') }}',
    wsPort: {{ env('PUSHER_PORT', 6001) }},
    wssPort: {{ env('PUSHER_PORT', 6001) }},
    forceTLS: false,
    encrypted: false,
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    cluster: '{{ env('PUSHER_APP_CLUSTER', 'mt1') }}',
    auth: {
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    }
});
```

The `{{ env('PUSHER_HOST', '127.0.0.1') }}` automatically reads from `.env` file:
- Local: Uses `127.0.0.1` (localhost)
- Production: Uses your domain/IP from `.env`

## Steps to Deploy to Production

### 1. Update .env on Live Server
```bash
# SSH into your live server
ssh user@your-server.com

# Navigate to project directory
cd /path/to/craftyart

# Edit .env file
nano .env

# Update PUSHER_HOST to your domain or IP
PUSHER_HOST=craftyart.in
# OR
PUSHER_HOST=123.45.67.89
```

### 2. Start Laravel WebSocket Server
```bash
# Install laravel-websockets if not already installed
composer require beyondcode/laravel-websockets

# Start WebSocket server
php artisan websockets:serve

# For production, use supervisor or systemd to keep it running
```

### 3. Configure Supervisor (Recommended for Production)
Create `/etc/supervisor/conf.d/websockets.conf`:
```ini
[program:websockets]
command=php /path/to/craftyart/artisan websockets:serve
directory=/path/to/craftyart
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/websockets.log
```

Then:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start websockets
```

### 4. Configure Nginx (if using Nginx)
Add to your nginx config:
```nginx
location /app/ {
    proxy_pass http://127.0.0.1:6001;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_cache_bypass $http_upgrade;
}
```

### 5. Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Testing WebSocket Connection

### 1. Check WebSocket Server is Running
```bash
# Check if port 6001 is listening
netstat -tulpn | grep 6001

# Or
ss -tulpn | grep 6001
```

### 2. Test from Browser Console
Open browser console on order_user or recent_expire page:
```javascript
// You should see these logs:
// 🚀 Initializing WebSocket connection...
// 🔄 WebSocket connecting...
// ✅ WebSocket CONNECTED! Real-time updates enabled
```

### 3. Test Real-Time Updates
1. Open order_user page in two browser windows
2. Enable followup on one window
3. Check if it updates in the other window immediately
4. Same test for recent_expire page

## Troubleshooting

### Issue: WebSocket not connecting
**Solution:**
1. Check PUSHER_HOST in .env matches your domain/IP
2. Ensure WebSocket server is running: `php artisan websockets:serve`
3. Check firewall allows port 6001
4. Check nginx/apache proxy configuration

### Issue: Works on localhost but not on live
**Solution:**
1. Verify PUSHER_HOST is set to live domain/IP (not 127.0.0.1)
2. Clear config cache: `php artisan config:clear`
3. Restart WebSocket server
4. Check browser console for connection errors

### Issue: SSL/HTTPS errors
**Solution:**
If your site uses HTTPS, you may need to use WSS (secure WebSocket):
```env
PUSHER_SCHEME=https
```

And configure SSL certificates for WebSocket server.

## Environment-Specific Settings

### Development (localhost)
```env
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

### Staging Server
```env
PUSHER_HOST=staging.craftyart.in
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

### Production Server
```env
PUSHER_HOST=craftyart.in
PUSHER_PORT=6001
PUSHER_SCHEME=http
# OR for HTTPS:
# PUSHER_SCHEME=https
```

## Security Considerations

1. **Firewall**: Only allow port 6001 from your application server
2. **Authentication**: WebSocket uses CSRF token for authentication
3. **SSL**: Consider using WSS (secure WebSocket) for production
4. **Rate Limiting**: Configure rate limiting in `config/websockets.php`

## Monitoring

### Check WebSocket Server Status
```bash
# Using supervisor
sudo supervisorctl status websockets

# Check logs
tail -f /var/log/websockets.log
```

### Monitor Connections
```bash
# Check active connections
php artisan websockets:statistics
```

## Files Already Configured
✅ `resources/views/order_user/index.blade.php` - Uses dynamic config
✅ `resources/views/recent_expire/index.blade.php` - Uses dynamic config
✅ `.env` - Local configuration
✅ `.env.production.example` - Production template

## No Code Changes Needed!
The code is already production-ready. Just update `.env` on your live server with the correct `PUSHER_HOST` value.
