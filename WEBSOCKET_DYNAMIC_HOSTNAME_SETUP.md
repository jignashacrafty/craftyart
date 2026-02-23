# WebSocket Dynamic Hostname Configuration

## Overview
Both `order_user` and `recent_expire` pages now use `window.location.hostname` for WebSocket connection, making them automatically work in any environment without .env changes.

## Configuration Applied

### Both Pages Now Use:
```javascript
const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
    wsHost: window.location.hostname,
    wsPort: 443,
    wssPort: 443,
    forceTLS: true,
    encrypted: true,
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

## How It Works

### `window.location.hostname`
This JavaScript property automatically returns the current domain/hostname:

| Environment | URL | `window.location.hostname` |
|-------------|-----|----------------------------|
| Local | http://localhost/craftyart/public/order_user | `localhost` |
| Local IP | http://192.168.1.100/craftyart/public/order_user | `192.168.1.100` |
| Production | https://craftyart.in/order_user | `craftyart.in` |
| Staging | https://staging.craftyart.in/order_user | `staging.craftyart.in` |

### Port Configuration
- **wsPort: 443** - Standard HTTPS port for secure WebSocket (WSS)
- **wssPort: 443** - Same as wsPort for secure connections
- **forceTLS: true** - Forces secure WebSocket connection (WSS)
- **encrypted: true** - Enables encryption for WebSocket data

## Benefits

### 1. Zero Configuration
✅ No need to update .env file when deploying
✅ Works automatically on localhost, staging, and production
✅ No hardcoded domains or IPs

### 2. Environment Agnostic
✅ Same code works everywhere
✅ Developers can use localhost, 127.0.0.1, or local IP
✅ Production uses actual domain automatically

### 3. Secure by Default
✅ Uses port 443 (HTTPS standard)
✅ Forces TLS encryption
✅ Encrypted WebSocket data

## Server Requirements

### WebSocket Server Must Listen on Port 443
Your WebSocket server needs to be configured to accept connections on port 443 with SSL/TLS.

### Option 1: Nginx Proxy (Recommended)
Configure Nginx to proxy WebSocket connections:

```nginx
# /etc/nginx/sites-available/craftyart.in

server {
    listen 443 ssl http2;
    server_name craftyart.in;

    ssl_certificate /path/to/ssl/certificate.crt;
    ssl_certificate_key /path/to/ssl/private.key;

    # Regular Laravel application
    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # WebSocket proxy
    location /app/ {
        proxy_pass http://127.0.0.1:6001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto https;
        proxy_cache_bypass $http_upgrade;
    }
}
```

### Option 2: Direct SSL on WebSocket Server
Configure Laravel WebSockets to use SSL directly:

**config/websockets.php:**
```php
'ssl' => [
    'local_cert' => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT', null),
    'local_pk' => env('LARAVEL_WEBSOCKETS_SSL_LOCAL_PK', null),
    'passphrase' => env('LARAVEL_WEBSOCKETS_SSL_PASSPHRASE', null),
],
```

**.env:**
```env
LARAVEL_WEBSOCKETS_SSL_LOCAL_CERT=/path/to/certificate.crt
LARAVEL_WEBSOCKETS_SSL_LOCAL_PK=/path/to/private.key
```

## Local Development Setup

### For Localhost (HTTP)
If testing on localhost without SSL, you may need to temporarily adjust:

```javascript
// For local testing only - NOT for production
const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
    wsHost: window.location.hostname,
    wsPort: 6001,  // Local WebSocket port
    wssPort: 6001,
    forceTLS: false,  // Disable for local HTTP
    encrypted: false,  // Disable for local HTTP
    // ... rest of config
});
```

But the current configuration (port 443, TLS enabled) is production-ready.

## Testing

### 1. Check WebSocket Connection
Open browser console on order_user or recent_expire page:

```javascript
// Expected logs:
// 🚀 Initializing WebSocket connection...
// 🔄 WebSocket connecting...
// ✅ WebSocket CONNECTED! Real-time updates enabled
```

### 2. Verify Hostname
Check what hostname is being used:
```javascript
console.log('WebSocket Host:', window.location.hostname);
```

### 3. Test Real-Time Updates
1. Open page in two browser tabs
2. Enable/disable followup in one tab
3. Verify it updates in the other tab immediately

## Troubleshooting

### Issue: Connection fails on localhost
**Cause:** Port 443 requires SSL, localhost typically uses HTTP

**Solution:** 
- Use production/staging server for testing
- OR temporarily modify config for local testing (see Local Development Setup above)
- OR set up local SSL certificate

### Issue: Connection fails on production
**Cause:** WebSocket server not listening on port 443 or SSL not configured

**Solution:**
1. Verify Nginx proxy is configured correctly
2. Check SSL certificates are valid
3. Ensure WebSocket server is running
4. Check firewall allows port 443

### Issue: Mixed content error (HTTP page trying WSS)
**Cause:** Page loaded over HTTP but trying to connect to WSS (secure WebSocket)

**Solution:**
- Ensure your site uses HTTPS
- Force HTTPS redirect in Nginx/Apache
- Update APP_URL in .env to use https://

## Environment Variables Still Needed

Only these are required in .env:

```env
PUSHER_APP_KEY=localkey
PUSHER_APP_CLUSTER=mt1
```

No need for:
- ~~PUSHER_HOST~~ (uses window.location.hostname)
- ~~PUSHER_PORT~~ (hardcoded to 443)
- ~~PUSHER_SCHEME~~ (always uses TLS)

## Files Modified
✅ `resources/views/order_user/index.blade.php` - Updated to use window.location.hostname
✅ `resources/views/recent_expire/index.blade.php` - Updated to use window.location.hostname

## Deployment Checklist

### Production Server
- [ ] Nginx configured to proxy WebSocket on port 443
- [ ] SSL certificate installed and valid
- [ ] WebSocket server running (php artisan websockets:serve)
- [ ] Firewall allows port 443
- [ ] Site uses HTTPS (not HTTP)

### Testing
- [ ] WebSocket connects successfully
- [ ] Real-time updates work
- [ ] No console errors
- [ ] Works across multiple tabs/users

## Security Notes

1. **TLS Encryption**: All WebSocket data is encrypted
2. **CSRF Protection**: Uses CSRF token for authentication
3. **Port 443**: Standard HTTPS port, typically allowed through firewalls
4. **No Hardcoded Credentials**: Uses environment variables

## Advantages Over Previous Setup

| Feature | Old Setup | New Setup |
|---------|-----------|-----------|
| Configuration | Requires .env update per environment | Zero configuration |
| Hostname | Hardcoded or from .env | Dynamic from browser |
| Port | Variable (6001 local, 443 prod) | Always 443 |
| Security | Mixed (HTTP local, HTTPS prod) | Always TLS/HTTPS |
| Deployment | Manual .env changes | Deploy and go |

## Summary

The WebSocket configuration now automatically adapts to any environment using `window.location.hostname`. This means:

✅ **Localhost**: Connects to `localhost:443`
✅ **Production**: Connects to `craftyart.in:443`
✅ **Staging**: Connects to `staging.craftyart.in:443`

No .env changes needed - just ensure your WebSocket server is accessible on port 443 with SSL!
