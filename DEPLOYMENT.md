# PHPConsoleLog - Deployment Guide

This guide shows you how to deploy PHPConsoleLog in your own PHP project.

## Quick Start (3 Steps)

### Step 1: Install the Package

In your project directory:

```bash
composer require phpconsolelog/phpconsolelog
```

### Step 2: Copy the Boilerplate Files

Copy the server and launcher scripts to your project root:

```bash
# Copy the server file (required)
cp vendor/phpconsolelog/phpconsolelog/server.php .

# Copy launcher scripts (choose what you need)
cp vendor/phpconsolelog/phpconsolelog/start-server.bat .      # Windows CMD
cp vendor/phpconsolelog/phpconsolelog/start-server.ps1 .      # Windows PowerShell
cp vendor/phpconsolelog/phpconsolelog/start-server.sh .       # Linux/Mac

# Make executable (Linux/Mac only)
chmod +x start-server.sh
```

### Step 3: Start the Server

Choose your preferred method:

**Windows (Batch):**
```cmd
start-server.bat                    # Default: port 8080, all interfaces
start-server.bat 9000               # Custom port
start-server.bat 9000 127.0.0.1     # Custom port and host
```

**Windows (PowerShell):**
```powershell
.\start-server.ps1                          # Default: port 8080, all interfaces
.\start-server.ps1 -Port 9000               # Custom port
.\start-server.ps1 -Port 9000 -Host "127.0.0.1"  # Custom port and host
```

**Linux/Mac:**
```bash
./start-server.sh                   # Default: port 8080, all interfaces
./start-server.sh 9000              # Custom port
./start-server.sh 9000 127.0.0.1    # Custom port and host
```

**Any Platform (PHP):**
```bash
php server.php                      # Default: port 8080, all interfaces
php server.php 9000                 # Custom port
php server.php 9000 127.0.0.1       # Custom port and host
```

**Using Composer:**
```bash
composer serve
```

## Using in Your Application

Once the server is running, use it in your PHP code:

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Initialize logger
$logger = new Logger('http://localhost:8080/logger', 'my-app');

// Start logging!
$logger->log('Application started');
$logger->info('User logged in', ['user_id' => 123]);
$logger->warning('Low disk space');
$logger->error('Database connection failed');
```

Then open the viewer in your browser:

```
http://localhost:8080/viewer/my-app
```

## Customization

### Change Port or Host

**Option A: Using command-line arguments (recommended for temporary changes):**

```bash
# Windows Batch
start-server.bat 9000 127.0.0.1

# PowerShell
.\start-server.ps1 -Port 9000 -Host "127.0.0.1"

# Linux/Mac
./start-server.sh 9000 127.0.0.1

# Direct PHP
php server.php 9000 127.0.0.1
```

**Option B: Edit the configuration in `server.php` (for permanent changes):**

```php
// CONFIGURATION - Edit these settings as needed
$port = $argv[1] ?? 9000;        // Change default port
$host = $argv[2] ?? '127.0.0.1'; // Change default host ('0.0.0.0' = all, '127.0.0.1' = local)
$bufferSize = 100;               // Number of messages to keep in history
```

### Multiple Log Streams

Use different keys for different parts of your application:

```php
$authLogger = new Logger('http://localhost:8080/logger', 'auth');
$apiLogger = new Logger('http://localhost:8080/logger', 'api');
$dbLogger = new Logger('http://localhost:8080/logger', 'database');
```

View them separately:
- http://localhost:8080/viewer/auth
- http://localhost:8080/viewer/api
- http://localhost:8080/viewer/database

### Disable in Production

Always disable logging in production environments:

```php
$logger = new Logger('http://localhost:8080/logger', 'my-app');

// Disable in production
if (getenv('APP_ENV') === 'production') {
    $logger->disable();
}
```

## Running as a Background Service

### Linux/Mac (systemd)

Create `/etc/systemd/system/phpconsolelog.service`:

```ini
[Unit]
Description=PHPConsoleLog Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/your-project
ExecStart=/usr/bin/php /var/www/your-project/server.php
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

Enable and start:

```bash
sudo systemctl enable phpconsolelog
sudo systemctl start phpconsolelog
sudo systemctl status phpconsolelog
```

### Windows (NSSM - Non-Sucking Service Manager)

1. Download NSSM from https://nssm.cc/download
2. Install the service:

```cmd
nssm install PHPConsoleLog "C:\php\php.exe" "C:\your-project\server.php"
nssm set PHPConsoleLog AppDirectory "C:\your-project"
nssm start PHPConsoleLog
```

### Docker

Create `Dockerfile`:

```dockerfile
FROM php:8.1-cli

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Expose port
EXPOSE 8080

# Start server
CMD ["php", "server.php"]
```

Create `docker-compose.yml`:

```yaml
version: '3.8'
services:
  phpconsolelog:
    build: .
    ports:
      - "8080:8080"
    volumes:
      - ./server.php:/app/server.php
    restart: unless-stopped
```

Run:

```bash
docker-compose up -d
```

## Network Configuration

### Accessing from Other Machines

If you want to access the server from other machines on your network:

1. Edit `server.php` and ensure `$host = '0.0.0.0';`
2. Find your machine's IP address:
   - Windows: `ipconfig`
   - Linux/Mac: `ip addr` or `ifconfig`
3. Use that IP in your client code:

```php
$logger = new Logger('http://192.168.1.100:8080/logger', 'my-app');
```

4. Open viewer at: `http://192.168.1.100:8080/viewer/my-app`

### Using with Port Forwarding

See `examples/port-forwarding-example.md` for detailed instructions on:
- SSH port forwarding
- Accessing servers on remote machines
- Development environment setups

## Security Considerations

⚠️ **Important:** PHPConsoleLog is designed for development and debugging only.

1. **Never use in production** - Disable logging in production environments
2. **Use random keys** - Don't use predictable keys like "app" or "test"
3. **Restrict access** - Use `127.0.0.1` instead of `0.0.0.0` when possible
4. **Firewall rules** - Block the port from external access
5. **No sensitive data** - Never log passwords, API keys, or personal information

## Troubleshooting

### "Port already in use"

Change the port in `server.php`:

```php
$port = 9000;  // Use a different port
```

### "Connection refused"

- Make sure the server is running
- Check firewall settings
- Verify the server URL in your client code
- Try using `127.0.0.1` instead of `localhost`

### "Composer not found"

Install Composer from https://getcomposer.org/

### "PHP not found"

- **Windows:** Download from https://windows.php.net/download/
- **Ubuntu/Debian:** `sudo apt-get install php-cli`
- **macOS:** `brew install php`
- **CentOS/RHEL:** `sudo yum install php-cli`

### Logs not appearing

1. Check the server is running
2. Ensure you're using the same key in client and viewer
3. Check browser console (F12) for WebSocket errors
4. Verify the server URL is correct
5. Try clearing browser cache

## Need Help?

- 📖 Read the [README.md](README.md) for full documentation
- 🚀 Check the [QUICKSTART.md](QUICKSTART.md) for a guided tutorial
- 💡 Browse [examples/](examples/) for code samples
- 🐛 Report issues on [GitHub](https://github.com/phpconsolelog/phpconsolelog/issues)

---

**Happy Debugging!** 🐛✨

