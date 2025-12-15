# PHPConsoleLog - Quick Reference Card

## Installation

```bash
composer require phpconsolelog/phpconsolelog
```

## Deployment (One-Time Setup)

```bash
# Copy server file
cp vendor/phpconsolelog/phpconsolelog/server.php .

# Copy launcher (pick your platform)
cp vendor/phpconsolelog/phpconsolelog/start-server.bat .      # Windows
cp vendor/phpconsolelog/phpconsolelog/start-server.ps1 .      # PowerShell
cp vendor/phpconsolelog/phpconsolelog/start-server.sh .       # Linux/Mac

# Make executable (Linux/Mac only)
chmod +x start-server.sh
```

## Start Server

| Platform | Default | Custom Port | Custom Port + Host |
|----------|---------|-------------|-------------------|
| **Windows** | `start-server.bat` | `start-server.bat 9000` | `start-server.bat 9000 127.0.0.1` |
| **PowerShell** | `.\start-server.ps1` | `.\start-server.ps1 -Port 9000` | `.\start-server.ps1 -Port 9000 -Host "127.0.0.1"` |
| **Linux/Mac** | `./start-server.sh` | `./start-server.sh 9000` | `./start-server.sh 9000 127.0.0.1` |
| **PHP** | `php server.php` | `php server.php 9000` | `php server.php 9000 127.0.0.1` |
| **Composer** | `composer serve` | N/A | N/A |

## Using in Your Code

```php
<?php
require_once 'vendor/autoload.php';

use PHPConsoleLog\Client\Logger;

// Create logger
$logger = new Logger('http://localhost:8080/logger', 'my-key');

// Log messages
$logger->log('Simple message');
$logger->info('Info message');
$logger->warning('Warning message');
$logger->error('Error message');
$logger->debug('Debug message');

// Log variables
$logger->log('User data:', $userData);
$logger->log('User:', $user, 'Action:', $action);

// Log exceptions
try {
    // code
} catch (Exception $e) {
    $logger->error('Error:', $e);
}
```

## View Logs

Open in browser:
```
http://localhost:8080/viewer/my-key
```

Replace `my-key` with your chosen identifier.

## Configuration

**Quick override (command-line):**
```bash
php server.php 9000 127.0.0.1  # port 9000, localhost only
```

**Permanent changes (edit `server.php`):**
```php
$port = $argv[1] ?? 9000;       // Change default port
$host = $argv[2] ?? '127.0.0.1'; // Change default host
$bufferSize = 100;              // Change message history size
```

## Multiple Log Streams

```php
$authLogger = new Logger('http://localhost:8080/logger', 'auth');
$apiLogger = new Logger('http://localhost:8080/logger', 'api');
$dbLogger = new Logger('http://localhost:8080/logger', 'db');
```

View at:
- http://localhost:8080/viewer/auth
- http://localhost:8080/viewer/api
- http://localhost:8080/viewer/db

## Disable in Production

```php
$logger = new Logger('http://localhost:8080/logger', 'my-key');

if (getenv('APP_ENV') === 'production') {
    $logger->disable();
}
```

## Troubleshooting

| Problem | Solution |
|---------|----------|
| **Port in use** | Change `$port` in `server.php` |
| **Connection refused** | Check server is running |
| **Logs not appearing** | Verify same key in client & viewer |
| **PHP not found** | Add PHP to PATH or install it |
| **Vendor not found** | Run `composer install` |

## Server Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| POST | `/logger` | Receive log messages |
| GET | `/viewer/{key}` | Web viewer interface |
| WS | `/ws` | WebSocket connection |

## Common Use Cases

### AJAX Debugging
```php
$logger = new Logger('http://localhost:8080/logger', 'ajax-debug');
$logger->log('Request:', $_POST);
// ... your code ...
$logger->log('Response:', $response);
echo json_encode($response);  // Logging doesn't interfere!
```

### Background Jobs
```php
$logger = new Logger('http://localhost:8080/logger', 'batch-job');
$logger->info('Starting batch job');
foreach ($items as $item) {
    $logger->log('Processing:', $item['id']);
    // process item
}
$logger->info('Completed');
```

### API Development
```php
$logger = new Logger('http://localhost:8080/logger', 'api');
$logger->log('Endpoint:', $_SERVER['REQUEST_URI']);
$logger->log('Method:', $_SERVER['REQUEST_METHOD']);
$logger->log('Body:', file_get_contents('php://input'));
```

## Security Notes

⚠️ **Development only** - Never use in production  
⚠️ **Use random keys** - Don't use predictable identifiers  
⚠️ **No sensitive data** - Never log passwords/API keys  
⚠️ **Restrict access** - Use firewall rules if needed  

## Learn More

- 📖 **Full Documentation**: [README.md](README.md)
- 🚀 **Quick Tutorial**: [QUICKSTART.md](QUICKSTART.md)
- 🔧 **Deployment Guide**: [DEPLOYMENT.md](DEPLOYMENT.md)
- 💡 **Examples**: [examples/](examples/)
- 📋 **Boilerplate Info**: [BOILERPLATE_SUMMARY.md](BOILERPLATE_SUMMARY.md)

---

**Keep this handy for quick reference!** 🚀

