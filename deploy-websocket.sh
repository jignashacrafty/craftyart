#!/bin/bash

# Laravel WebSocket Deployment Script
# This script automates the WebSocket server setup on production

echo "🚀 Laravel WebSocket Deployment Script"
echo "========================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Get project directory
PROJECT_DIR=$(pwd)
echo "📁 Project Directory: $PROJECT_DIR"
echo ""

# Step 1: Check if Supervisor is installed
echo "1️⃣  Checking Supervisor installation..."
if ! command -v supervisorctl &> /dev/null; then
    echo -e "${YELLOW}⚠️  Supervisor not found. Installing...${NC}"
    sudo apt-get update
    sudo apt-get install -y supervisor
    echo -e "${GREEN}✅ Supervisor installed${NC}"
else
    echo -e "${GREEN}✅ Supervisor already installed${NC}"
fi
echo ""

# Step 2: Check if port 6001 is open
echo "2️⃣  Checking if port 6001 is available..."
if lsof -Pi :6001 -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${YELLOW}⚠️  Port 6001 is already in use${NC}"
    echo "   Run: sudo lsof -i :6001 to see what's using it"
else
    echo -e "${GREEN}✅ Port 6001 is available${NC}"
fi
echo ""

# Step 3: Configure firewall
echo "3️⃣  Configuring firewall..."
if command -v ufw &> /dev/null; then
    sudo ufw allow 6001/tcp
    echo -e "${GREEN}✅ Port 6001 opened in UFW firewall${NC}"
else
    echo -e "${YELLOW}⚠️  UFW not found. Please open port 6001 manually${NC}"
fi
echo ""

# Step 4: Create Supervisor configuration
echo "4️⃣  Creating Supervisor configuration..."
SUPERVISOR_CONF="/etc/supervisor/conf.d/laravel-websocket.conf"

sudo tee $SUPERVISOR_CONF > /dev/null <<EOF
[program:laravel-websocket]
command=php $PROJECT_DIR/artisan websockets:serve
directory=$PROJECT_DIR
process_name=%(program_name)s_%(process_num)02d
numprocs=1
autostart=true
autorestart=true
startsecs=1
startretries=3
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/supervisor/websocket.log
stdout_logfile_maxbytes=10MB
stdout_logfile_backups=10
stopsignal=QUIT
stopwaitsecs=10
environment=LARAVEL_ENV="production"
priority=999
EOF

echo -e "${GREEN}✅ Supervisor configuration created${NC}"
echo ""

# Step 5: Reload Supervisor
echo "5️⃣  Reloading Supervisor..."
sudo supervisorctl reread
sudo supervisorctl update
echo -e "${GREEN}✅ Supervisor reloaded${NC}"
echo ""

# Step 6: Start WebSocket server
echo "6️⃣  Starting WebSocket server..."
sudo supervisorctl start laravel-websocket:*
sleep 2
echo -e "${GREEN}✅ WebSocket server started${NC}"
echo ""

# Step 7: Check status
echo "7️⃣  Checking WebSocket server status..."
sudo supervisorctl status laravel-websocket:*
echo ""

# Step 8: Check if port is listening
echo "8️⃣  Verifying port 6001 is listening..."
if lsof -Pi :6001 -sTCP:LISTEN -t >/dev/null ; then
    echo -e "${GREEN}✅ WebSocket server is listening on port 6001${NC}"
else
    echo -e "${RED}❌ WebSocket server is NOT listening on port 6001${NC}"
    echo "   Check logs: sudo tail -f /var/log/supervisor/websocket.log"
fi
echo ""

# Step 9: Clear Laravel cache
echo "9️⃣  Clearing Laravel cache..."
php artisan config:clear
php artisan cache:clear
echo -e "${GREEN}✅ Cache cleared${NC}"
echo ""

# Final instructions
echo "========================================"
echo -e "${GREEN}🎉 WebSocket Deployment Complete!${NC}"
echo "========================================"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Update your .env file:"
echo "   PUSHER_HOST=your-domain.com"
echo "   BROADCAST_DRIVER=pusher"
echo ""
echo "2. Update frontend code to use:"
echo "   wsHost: 'your-domain.com'"
echo "   wsPort: 6001"
echo ""
echo "3. Test WebSocket connection:"
echo "   Open browser console and run test code"
echo ""
echo "4. View logs:"
echo "   sudo tail -f /var/log/supervisor/websocket.log"
echo ""
echo "5. Useful commands:"
echo "   Start:   sudo supervisorctl start laravel-websocket:*"
echo "   Stop:    sudo supervisorctl stop laravel-websocket:*"
echo "   Restart: sudo supervisorctl restart laravel-websocket:*"
echo "   Status:  sudo supervisorctl status"
echo ""
echo "========================================"
echo -e "${GREEN}✅ All Done!${NC}"
echo "========================================"
