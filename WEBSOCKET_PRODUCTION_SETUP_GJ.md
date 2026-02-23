# WebSocket Production Setup Guide (ગુજરાતી)

## વર્તમાન સ્થિતિ
`order_user` અને `recent_expire` બંને pages પહેલેથી જ `.env` file માંથી dynamic WebSocket settings વાપરવા માટે configured છે. કોઈ code changes ની જરૂર નથી!

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

### Production (live server પર .env)
```env
BROADCAST_DRIVER=pusher
BROADCAST_CONNECTION=pusher

PUSHER_APP_ID=local
PUSHER_APP_KEY=localkey
PUSHER_APP_SECRET=localsecret

# ⚠️ મહત્વપૂર્ણ: આને તમારા live domain અથવા IP માં બદલો
PUSHER_HOST=craftyart.in
# અથવા IP address વાપરો:
# PUSHER_HOST=123.45.67.89

PUSHER_PORT=6001
PUSHER_SCHEME=http
PUSHER_APP_CLUSTER=mt1
```

## કેવી રીતે કામ કરે છે

બંને pages આ configuration વાપરે છે:
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

`{{ env('PUSHER_HOST', '127.0.0.1') }}` આપોઆપ `.env` file માંથી વાંચે છે:
- Local: `127.0.0.1` (localhost) વાપરે છે
- Production: તમારા domain/IP ને `.env` માંથી વાપરે છે

## Production પર Deploy કરવાના Steps

### 1. Live Server પર .env Update કરો
```bash
# તમારા live server માં SSH કરો
ssh user@your-server.com

# Project directory માં જાઓ
cd /path/to/craftyart

# .env file edit કરો
nano .env

# PUSHER_HOST ને તમારા domain અથવા IP માં update કરો
PUSHER_HOST=craftyart.in
# અથવા
PUSHER_HOST=123.45.67.89
```

### 2. Laravel WebSocket Server Start કરો
```bash
# laravel-websockets install કરો જો પહેલેથી installed ન હોય
composer require beyondcode/laravel-websockets

# WebSocket server start કરો
php artisan websockets:serve

# Production માટે, supervisor અથવા systemd વાપરો જેથી તે running રહે
```

### 3. Supervisor Configure કરો (Production માટે Recommended)
`/etc/supervisor/conf.d/websockets.conf` બનાવો:
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

પછી:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start websockets
```

### 4. Nginx Configure કરો (જો Nginx વાપરો છો)
તમારા nginx config માં ઉમેરો:
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

### 5. Cache Clear કરો
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## WebSocket Connection Test કરો

### 1. Check કરો કે WebSocket Server Running છે
```bash
# Check કરો કે port 6001 listening છે
netstat -tulpn | grep 6001

# અથવા
ss -tulpn | grep 6001
```

### 2. Browser Console માંથી Test કરો
order_user અથવા recent_expire page પર browser console ખોલો:
```javascript
// તમને આ logs દેખાવા જોઈએ:
// 🚀 Initializing WebSocket connection...
// 🔄 WebSocket connecting...
// ✅ WebSocket CONNECTED! Real-time updates enabled
```

### 3. Real-Time Updates Test કરો
1. બે browser windows માં order_user page ખોલો
2. એક window માં followup enable કરો
3. Check કરો કે તે બીજી window માં તરત જ update થાય છે
4. recent_expire page માટે same test કરો

## Troubleshooting

### Issue: WebSocket connect થતું નથી
**Solution:**
1. Check કરો કે .env માં PUSHER_HOST તમારા domain/IP સાથે match થાય છે
2. Ensure કરો કે WebSocket server running છે: `php artisan websockets:serve`
3. Check કરો કે firewall port 6001 ને allow કરે છે
4. nginx/apache proxy configuration check કરો

### Issue: Localhost પર કામ કરે છે પણ live પર નહીં
**Solution:**
1. Verify કરો કે PUSHER_HOST live domain/IP પર set છે (127.0.0.1 નહીં)
2. Config cache clear કરો: `php artisan config:clear`
3. WebSocket server restart કરો
4. Browser console માં connection errors check કરો

### Issue: SSL/HTTPS errors
**Solution:**
જો તમારી site HTTPS વાપરે છે, તો તમારે WSS (secure WebSocket) વાપરવાની જરૂર પડી શકે:
```env
PUSHER_SCHEME=https
```

અને WebSocket server માટે SSL certificates configure કરો.

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
# અથવા HTTPS માટે:
# PUSHER_SCHEME=https
```

## Security Considerations

1. **Firewall**: ફક્ત તમારા application server માંથી port 6001 ને allow કરો
2. **Authentication**: WebSocket authentication માટે CSRF token વાપરે છે
3. **SSL**: Production માટે WSS (secure WebSocket) વાપરવાનું consider કરો
4. **Rate Limiting**: `config/websockets.php` માં rate limiting configure કરો

## Monitoring

### WebSocket Server Status Check કરો
```bash
# Supervisor વાપરીને
sudo supervisorctl status websockets

# Logs check કરો
tail -f /var/log/websockets.log
```

### Connections Monitor કરો
```bash
# Active connections check કરો
php artisan websockets:statistics
```

## Files Already Configured
✅ `resources/views/order_user/index.blade.php` - Dynamic config વાપરે છે
✅ `resources/views/recent_expire/index.blade.php` - Dynamic config વાપરે છે
✅ `.env` - Local configuration
✅ `.env.production.example` - Production template

## કોઈ Code Changes ની જરૂર નથી!
Code પહેલેથી જ production-ready છે. ફક્ત તમારા live server પર `.env` માં સાચી `PUSHER_HOST` value સાથે update કરો.

## Quick Setup માટે

Live server પર ફક્ત આ કરો:
```bash
# 1. .env edit કરો
nano .env

# 2. આ line શોધો અને બદલો:
PUSHER_HOST=127.0.0.1
# આમાં બદલો:
PUSHER_HOST=craftyart.in

# 3. Cache clear કરો
php artisan config:clear

# 4. WebSocket server restart કરો
sudo supervisorctl restart websockets

# બસ! હવે live પર કામ કરશે
```
