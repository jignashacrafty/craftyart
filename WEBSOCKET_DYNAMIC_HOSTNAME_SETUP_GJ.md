# WebSocket Dynamic Hostname Configuration (ગુજરાતી)

## ઝાંખી
`order_user` અને `recent_expire` બંને pages હવે WebSocket connection માટે `window.location.hostname` વાપરે છે, જે તેમને કોઈપણ environment માં .env changes વગર આપોઆપ કામ કરવા દે છે.

## લાગુ કરેલ Configuration

### બંને Pages હવે આ વાપરે છે:
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

## કેવી રીતે કામ કરે છે

### `window.location.hostname`
આ JavaScript property આપોઆપ current domain/hostname return કરે છે:

| Environment | URL | `window.location.hostname` |
|-------------|-----|----------------------------|
| Local | http://localhost/craftyart/public/order_user | `localhost` |
| Local IP | http://192.168.1.100/craftyart/public/order_user | `192.168.1.100` |
| Production | https://craftyart.in/order_user | `craftyart.in` |
| Staging | https://staging.craftyart.in/order_user | `staging.craftyart.in` |

### Port Configuration
- **wsPort: 443** - Secure WebSocket (WSS) માટે standard HTTPS port
- **wssPort: 443** - Secure connections માટે wsPort જેવું જ
- **forceTLS: true** - Secure WebSocket connection (WSS) force કરે છે
- **encrypted: true** - WebSocket data માટે encryption enable કરે છે

## ફાયદા

### 1. Zero Configuration
✅ Deploy કરતી વખતે .env file update કરવાની જરૂર નથી
✅ localhost, staging, અને production પર આપોઆપ કામ કરે છે
✅ કોઈ hardcoded domains અથવા IPs નથી

### 2. Environment Agnostic
✅ બધે same code કામ કરે છે
✅ Developers localhost, 127.0.0.1, અથવા local IP વાપરી શકે છે
✅ Production આપોઆપ actual domain વાપરે છે

### 3. Secure by Default
✅ Port 443 (HTTPS standard) વાપરે છે
✅ TLS encryption force કરે છે
✅ Encrypted WebSocket data

## Server Requirements

### WebSocket Server Port 443 પર Listen કરવું જોઈએ
તમારા WebSocket server ને SSL/TLS સાથે port 443 પર connections accept કરવા માટે configured હોવું જોઈએ.

### Option 1: Nginx Proxy (Recommended)
WebSocket connections ને proxy કરવા માટે Nginx configure કરો:

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

### Option 2: WebSocket Server પર Direct SSL
Laravel WebSockets ને directly SSL વાપરવા માટે configure કરો:

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

### Localhost (HTTP) માટે
જો SSL વગર localhost પર testing કરો છો, તો તમારે temporarily adjust કરવાની જરૂર પડી શકે:

```javascript
// ફક્ત local testing માટે - Production માટે નહીં
const pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
    wsHost: window.location.hostname,
    wsPort: 6001,  // Local WebSocket port
    wssPort: 6001,
    forceTLS: false,  // Local HTTP માટે disable કરો
    encrypted: false,  // Local HTTP માટે disable કરો
    // ... બાકીની config
});
```

પરંતુ current configuration (port 443, TLS enabled) production-ready છે.

## Testing

### 1. WebSocket Connection Check કરો
order_user અથવા recent_expire page પર browser console ખોલો:

```javascript
// Expected logs:
// 🚀 Initializing WebSocket connection...
// 🔄 WebSocket connecting...
// ✅ WebSocket CONNECTED! Real-time updates enabled
```

### 2. Hostname Verify કરો
Check કરો કે કયું hostname વપરાઈ રહ્યું છે:
```javascript
console.log('WebSocket Host:', window.location.hostname);
```

### 3. Real-Time Updates Test કરો
1. બે browser tabs માં page ખોલો
2. એક tab માં followup enable/disable કરો
3. Verify કરો કે તે બીજી tab માં તરત જ update થાય છે

## Troubleshooting

### Issue: Localhost પર connection fail થાય છે
**કારણ:** Port 443 ને SSL જોઈએ છે, localhost સામાન્ય રીતે HTTP વાપરે છે

**Solution:** 
- Testing માટે production/staging server વાપરો
- અથવા local testing માટે temporarily config modify કરો (ઉપર Local Development Setup જુઓ)
- અથવા local SSL certificate set up કરો

### Issue: Production પર connection fail થાય છે
**કારણ:** WebSocket server port 443 પર listening નથી અથવા SSL configured નથી

**Solution:**
1. Verify કરો કે Nginx proxy correctly configured છે
2. SSL certificates valid છે તે check કરો
3. Ensure કરો કે WebSocket server running છે
4. Firewall port 443 ને allow કરે છે તે check કરો

### Issue: Mixed content error (HTTP page WSS try કરે છે)
**કારણ:** Page HTTP પર loaded છે પરંતુ WSS (secure WebSocket) ને connect કરવાનો પ્રયાસ કરે છે

**Solution:**
- Ensure કરો કે તમારી site HTTPS વાપરે છે
- Nginx/Apache માં HTTPS redirect force કરો
- .env માં APP_URL ને https:// વાપરવા માટે update કરો

## હજુ પણ જરૂરી Environment Variables

.env માં ફક્ત આ જરૂરી છે:

```env
PUSHER_APP_KEY=localkey
PUSHER_APP_CLUSTER=mt1
```

જરૂર નથી:
- ~~PUSHER_HOST~~ (window.location.hostname વાપરે છે)
- ~~PUSHER_PORT~~ (443 પર hardcoded છે)
- ~~PUSHER_SCHEME~~ (હંમેશા TLS વાપરે છે)

## Modified Files
✅ `resources/views/order_user/index.blade.php` - window.location.hostname વાપરવા માટે updated
✅ `resources/views/recent_expire/index.blade.php` - window.location.hostname વાપરવા માટે updated

## Deployment Checklist

### Production Server
- [ ] Nginx port 443 પર WebSocket proxy કરવા માટે configured છે
- [ ] SSL certificate installed અને valid છે
- [ ] WebSocket server running છે (php artisan websockets:serve)
- [ ] Firewall port 443 ને allow કરે છે
- [ ] Site HTTPS વાપરે છે (HTTP નહીં)

### Testing
- [ ] WebSocket successfully connect થાય છે
- [ ] Real-time updates કામ કરે છે
- [ ] Console માં કોઈ errors નથી
- [ ] Multiple tabs/users માં કામ કરે છે

## Security Notes

1. **TLS Encryption**: બધો WebSocket data encrypted છે
2. **CSRF Protection**: Authentication માટે CSRF token વાપરે છે
3. **Port 443**: Standard HTTPS port, સામાન્ય રીતે firewalls દ્વારા allowed
4. **No Hardcoded Credentials**: Environment variables વાપરે છે

## પહેલાના Setup કરતાં ફાયદા

| Feature | જૂનું Setup | નવું Setup |
|---------|-----------|-----------|
| Configuration | દરેક environment માટે .env update જરૂરી | Zero configuration |
| Hostname | Hardcoded અથવા .env માંથી | Browser માંથી dynamic |
| Port | Variable (6001 local, 443 prod) | હંમેશા 443 |
| Security | Mixed (HTTP local, HTTPS prod) | હંમેશા TLS/HTTPS |
| Deployment | Manual .env changes | Deploy and go |

## સારાંશ

WebSocket configuration હવે `window.location.hostname` વાપરીને કોઈપણ environment માં આપોઆપ adapt થાય છે. આનો અર્થ:

✅ **Localhost**: `localhost:443` ને connect થાય છે
✅ **Production**: `craftyart.in:443` ને connect થાય છે
✅ **Staging**: `staging.craftyart.in:443` ને connect થાય છે

કોઈ .env changes જરૂરી નથી - ફક્ત ensure કરો કે તમારું WebSocket server SSL સાથે port 443 પર accessible છે!

## Quick Setup

Production server પર:
```bash
# 1. Nginx WebSocket proxy configure કરો (ઉપર જુઓ)
# 2. SSL certificate install કરો
# 3. WebSocket server start કરો
php artisan websockets:serve

# 4. Supervisor સાથે permanent રાખો
sudo supervisorctl start websockets

# બસ! હવે આપોઆપ કામ કરશે
```
