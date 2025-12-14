# Your Port Forwarding Scenario - Ready to Use! ✅

## Your Setup

```
Internet Clients
      ↓
http://example.com:8181
      ↓
[Your Router/Firewall]
  Port Forward: 8181 → 192.168.1.11:8080
      ↓
PHPConsoleLog Server
  192.168.1.11:8080
```

## ✅ Good News: It Will Work!

The code has been **specifically enhanced** to handle your exact scenario. Here's what happens:

### When External Clients Connect

1. **Client sends logs:**
   ```php
   $logger = new Logger('http://example.com:8181/logger', 'my-key');
   $logger->log('Hello!');
   ```
   - HTTP POST goes to `example.com:8181`
   - Router forwards to `192.168.1.11:8080`
   - Server receives and processes ✅

2. **User opens viewer:**
   - Browser navigates to `http://example.com:8181/viewer/my-key`
   - Router forwards request to `192.168.1.11:8080`
   - Server generates HTML with WebSocket URL: `ws://example.com:8181/ws`
   - Browser connects WebSocket to `ws://example.com:8181/ws`
   - Router forwards WebSocket connection to `192.168.1.11:8080` ✅

3. **Real-time logs appear in browser!** 🎉

## Step-by-Step Setup

### 1. Configure Port Forwarding on Your Router

```
External Port: 8181
Internal IP: 192.168.1.11
Internal Port: 8080
Protocol: TCP
```

### 2. Start the Server (on 192.168.1.11)

```bash
cd /path/to/phpconsolelog
php examples/server-start.php 8080 0.0.0.0
```

**Important:** Use `0.0.0.0` to listen on all network interfaces!

You'll see:
```
╔═══════════════════════════════════════════════════════════════╗
║              PHPConsoleLog Server                             ║
╚═══════════════════════════════════════════════════════════════╝

Starting server on 0.0.0.0:8080
Binding to: 0.0.0.0:8080

Endpoints:
  • POST http://0.0.0.0:8080/logger  - Log messages endpoint
  • GET  http://0.0.0.0:8080/viewer/{key} - Web viewer
  • WS   ws://0.0.0.0:8080/ws        - WebSocket endpoint

Server is running...
```

### 3. Configure Your Firewall (on 192.168.1.11)

```bash
# Windows
netsh advfirewall firewall add rule name="PHPConsoleLog" dir=in action=allow protocol=TCP localport=8080

# Linux
sudo ufw allow 8080/tcp
```

### 4. Client Configuration (External Users)

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Use your external domain and port
$logger = new Logger('http://example.com:8181/logger', 'my-app-key');

// Start logging
$logger->log('Application started');
$logger->info('User logged in', ['user_id' => 123]);
$logger->error('Something went wrong!', $exception);
```

### 5. Open Viewer (From Anywhere)

Open in browser: `http://example.com:8181/viewer/my-app-key`

## What Makes This Work?

### Dynamic WebSocket URL Detection

The server now has **intelligent URL construction** that:

1. **Extracts the host and port from incoming HTTP requests**
   - When you access `http://example.com:8181/viewer/key`
   - Server sees: `Host: example.com:8181`
   - Generates: `ws://example.com:8181/ws`

2. **Supports proxy headers** (for advanced setups)
   - `X-Forwarded-Host`
   - `X-Forwarded-Port`
   - `X-Forwarded-Proto`

3. **Automatically handles standard ports**
   - Port 80 → `ws://example.com/ws` (no port in URL)
   - Port 443 + HTTPS → `wss://example.com/ws` (secure WebSocket)
   - Custom ports → `ws://example.com:8181/ws` (port included)

## Testing Your Setup

### Test 1: Check if server is accessible externally

From **outside your network** (use your phone's mobile data or ask a friend):

```bash
curl -X POST http://example.com:8181/logger \
  -H "Content-Type: application/json" \
  -d '{"key":"test","level":"info","data":["Test from external"],"timestamp":1234567890}'
```

Should return: `{"success":true}`

### Test 2: Check viewer access

Open in browser: `http://example.com:8181/viewer/test`

You should see the viewer interface load.

### Test 3: Check WebSocket connection

1. Open browser console (F12)
2. Go to `http://example.com:8181/viewer/test`
3. Check console for: `WebSocket connected`

### Test 4: Full integration test

```bash
# Run the client example with your domain
php examples/client-example.php
```

Update the client example to use your domain first:
```php
$logger = new Logger('http://example.com:8181/logger', 'my-app-key');
```

## Multiple Access Methods

Your setup supports both internal and external access simultaneously:

### External Users (Internet)
- URL: `http://example.com:8181`
- Routing: Internet → Router → 192.168.1.11:8080

### Internal Users (Same LAN)
- URL: `http://192.168.1.11:8080`
- Routing: Direct connection (faster!)

Both will work perfectly! The server automatically generates the correct WebSocket URL based on how it's accessed.

## Troubleshooting

### Problem: Viewer loads but doesn't connect

**Cause:** WebSocket upgrade might be blocked

**Solution:** Ensure your router supports WebSocket (most modern routers do). Try:
- Update router firmware
- Check if router has "Application Layer Gateway" (ALG) settings
- Test with a direct IP temporarily: `http://[your-public-ip]:8181`

### Problem: Connection refused

**Cause:** Firewall or port forwarding issue

**Check:**
```bash
# On the server machine (192.168.1.11)
netstat -an | findstr 8080

# Should show:
# TCP    0.0.0.0:8080    0.0.0.0:0    LISTENING
```

### Problem: Works internally but not externally

**Cause:** Port forwarding not configured correctly

**Check:**
- Verify router port forwarding rule is active
- Test with online port checker: https://www.yougetsignal.com/tools/open-ports/
- Check if your ISP blocks port 8181 (some ISPs block certain ports)

## Advanced: HTTPS Support (Optional)

For production use, consider adding HTTPS:

### Option 1: Reverse Proxy (Recommended)

Install nginx on the same machine:

```nginx
server {
    listen 443 ssl;
    server_name example.com;
    
    ssl_certificate /path/to/fullchain.pem;
    ssl_certificate_key /path/to/privkey.pem;
    
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

Then:
- Forward port 443 instead of 8181
- Access via: `https://example.com`
- WebSocket will automatically use `wss://` (secure)

### Option 2: Cloudflare Tunnel (Easiest)

Use Cloudflare Tunnel (free) - no port forwarding needed!

## Production Checklist

- [ ] Server starts automatically on boot (systemd/Task Scheduler)
- [ ] Firewall configured on server
- [ ] Port forwarding configured on router
- [ ] DNS pointing to your public IP
- [ ] Dynamic DNS configured (if your ISP changes your IP)
- [ ] Monitoring/logging set up
- [ ] Consider HTTPS for production use
- [ ] Test from external network
- [ ] Document the setup for your team

## Summary

✅ **Your scenario is fully supported!**

The code has been enhanced to:
- Dynamically detect the correct WebSocket URL
- Support port forwarding and NAT
- Handle proxy headers for advanced setups
- Work with both internal and external access

**Just start the server with `0.0.0.0` and configure your port forwarding - it will work!** 🚀
