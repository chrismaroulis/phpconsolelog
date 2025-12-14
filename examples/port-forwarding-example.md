# Port Forwarding / NAT Setup Guide

This guide explains how to use PHPConsoleLog when the server is behind NAT/port forwarding.

## Scenario

- **Internal server:** 192.168.1.11:8080 (on your LAN)
- **External domain:** example.com:8181 (port forwarded)
- **Clients:** Connect from outside your LAN using example.com:8181

## Network Setup

### 1. Port Forwarding Configuration

Configure your router to forward:
```
External: example.com:8181 → Internal: 192.168.1.11:8080
```

### 2. DNS Configuration

Ensure your domain points to your public IP:
```
example.com → Your Public IP Address
```

## Server Setup

### Start the server on the internal machine

```bash
# On machine 192.168.1.11
php examples/server-start.php 8080 0.0.0.0
```

**Important:** Use `0.0.0.0` to listen on all network interfaces, allowing:
- Local access: `http://192.168.1.11:8080`
- External access: `http://example.com:8181` (via port forwarding)

The server will output:
```
Starting server on 0.0.0.0:8080
Binding to: 0.0.0.0:8080

Endpoints:
  • POST http://0.0.0.0:8080/logger  - Log messages endpoint
  • GET  http://0.0.0.0:8080/viewer/{key} - Web viewer
  • WS   ws://0.0.0.0:8080/ws        - WebSocket endpoint
```

## Client Usage

### External Clients (Outside LAN)

```php
<?php
// Use the external domain and port
$logger = new \PHPConsoleLog\Client\Logger(
    'http://example.com:8181/logger',
    'my-app-key'
);

$logger->log('Hello from external client!');
```

**View logs at:** `http://example.com:8181/viewer/my-app-key`

### Internal Clients (Same LAN)

```php
<?php
// Use the internal IP for faster performance (no router hop)
$logger = new \PHPConsoleLog\Client\Logger(
    'http://192.168.1.11:8080/logger',
    'my-app-key'
);

$logger->log('Hello from internal client!');
```

**View logs at:** `http://192.168.1.11:8080/viewer/my-app-key`

## How It Works

### Dynamic WebSocket URL Generation

The viewer automatically detects the correct WebSocket URL based on how you access it:

1. **External access:** `http://example.com:8181/viewer/key`
   - WebSocket connects to: `ws://example.com:8181/ws` ✅

2. **Internal access:** `http://192.168.1.11:8080/viewer/key`
   - WebSocket connects to: `ws://192.168.1.11:8080/ws` ✅

### Proxy Header Support

The server automatically handles:
- `X-Forwarded-Host` - The original host requested
- `X-Forwarded-Port` - The original port requested
- `X-Forwarded-Proto` - The original protocol (http/https)
- `Host` header with port - Standard host header

This ensures compatibility with:
- Direct connections
- NAT/Port forwarding
- Reverse proxies (nginx, Apache, Caddy)
- Cloud load balancers

## Firewall Configuration

Ensure your firewall allows:

### On the server machine (192.168.1.11)
```bash
# Allow incoming connections on port 8080
sudo ufw allow 8080/tcp
```

### On the router
- Forward external port 8181 to internal 192.168.1.11:8080
- Allow incoming connections on port 8181

## Testing

### Test external access:
```bash
# From outside your LAN
curl -X POST http://example.com:8181/logger \
  -H "Content-Type: application/json" \
  -d '{"key":"test","level":"info","data":["Test message"],"timestamp":1234567890}'
```

### Test internal access:
```bash
# From inside your LAN
curl -X POST http://192.168.1.11:8080/logger \
  -H "Content-Type: application/json" \
  -d '{"key":"test","level":"info","data":["Test message"],"timestamp":1234567890}'
```

Both should return:
```json
{"success":true}
```

## HTTPS / WSS (Secure WebSockets)

If you need HTTPS support, use a reverse proxy like nginx:

### Nginx configuration example:
```nginx
server {
    listen 443 ssl;
    server_name example.com;
    
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    
    location / {
        proxy_pass http://192.168.1.11:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }
}
```

The server will automatically detect HTTPS and use `wss://` for WebSockets!

## Troubleshooting

### Viewer connects but no logs appear
- Check if port forwarding is working for both HTTP and WebSocket
- WebSocket upgrades must pass through your router/firewall
- Check browser console for WebSocket connection errors

### Cannot access from external network
- Verify port forwarding configuration
- Check firewall rules on both router and server
- Ensure your public IP hasn't changed (use dynamic DNS if needed)

### Mixed content warnings in browser
- If your website uses HTTPS, you need to use WSS (secure WebSockets)
- Set up a reverse proxy with SSL/TLS as shown above

## Performance Tips

### For internal clients
Use the internal IP (192.168.1.11:8080) for:
- Lower latency (no router hop)
- Better performance
- No bandwidth usage on WAN connection

### For external clients
Use the external domain (example.com:8181) when:
- Client is outside your LAN
- You need consistent URLs regardless of location
- You're using SSL/TLS with a reverse proxy
